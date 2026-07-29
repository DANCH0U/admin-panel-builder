<?php

namespace App\AdminPanel\Engine;

use App\AdminPanel\Engine\Filters\FilterPipeline;
use App\AdminPanel\Engine\Query\QueryContext;
use App\AdminPanel\Engine\Query\QueryPipeline;
use App\AdminPanel\Engine\Query\Stages\BaseQueryStage;
use App\AdminPanel\Engine\Query\Stages\FilterStage;
use App\AdminPanel\Engine\Query\Stages\RelationStage;
use App\AdminPanel\Engine\Query\Stages\SearchStage;
use App\AdminPanel\Engine\Query\Stages\SortStage;
use App\AdminPanel\Engine\Query\Stages\TabStage;
use App\AdminPanel\Engine\Search\SearchPipeline;
use App\AdminPanel\Notifications\Notify;
use App\AdminPanel\Tables\Tabs\TabCollection;
use Illuminate\Http\Request;

/**
 * Central orchestrator for the Admin Data Grid.
 * Self-contained — no service provider registration required.
 */
class DataGridEngine
{
    private QueryPipeline $pipeline;

    public function __construct()
    {
        $this->pipeline = self::defaultPipeline();
    }

    public static function make(): static
    {
        return new static();
    }

    public function usingPipeline(QueryPipeline $pipeline): static
    {
        $this->pipeline = $pipeline;

        return $this;
    }

    public static function defaultPipeline(): QueryPipeline
    {
        $search = new SearchPipeline();
        $filters = new FilterPipeline();

        return new QueryPipeline([
            new BaseQueryStage(),
            new RelationStage(),
            new TabStage(),
            new SearchStage($search),
            new FilterStage($filters),
            new SortStage(),
        ]);
    }

    public function handle(BaseResource $resource, Request $request): array|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $schema = $resource->schema();
        $model = $resource->getModel();

        if ($request->has('export')) {
            return $this->handleExport($resource, $request, $schema);
        }

        $context = new QueryContext($model::query(), $request, $schema);
        $context = $this->pipeline->process($context);

        $paginator = $context->query->paginate(
            $context->getPerPage(),
            ['*'],
            'page',
            $context->getPage()
        );

        $columns = $schema['columns'] ?? [];
        $items = array_map(function ($item) use ($columns) {
            $record = $item->toArray();
            $row = $record;

            foreach ($columns as $col) {
                if (is_object($col) && method_exists($col, 'transformValue')) {
                    $val = data_get($record, $col->getName());
                    $row[$col->getName()] = $col->transformValue($val, $record);
                }
            }

            return $row;
        }, $paginator->items());

        $transformed = $resource->transform($items, $schema['actions'] ?? []);

        $tabSchema = $schema['tabs'] ?? null;

        $columns = $schema['columns'] ?? [];
        $columns[] = ['name' => 'table_actions', 'type' => 'table_actions', 'label' => '', 'hidden' => false];

        return [
            'records' => $transformed,
            'current_page' => $paginator->currentPage(),
            'total_pages' => $paginator->lastPage(),
            'total_records' => $paginator->total(),
            'schema' => [
                'tabs' => $tabSchema instanceof TabCollection
                    ? $this->serializeTabs($tabSchema)
                    : [],
                'search' => isset($schema['search_columns'])
                    ? ['placeholder' => $schema['search_placeholder'] ?? 'Search...']
                    : null,
                'columns' => array_map(fn ($c) => is_object($c) ? $c->toArray() : $c, $columns),
                'filters' => array_map(fn ($f) => $f->toArray(), $schema['filters'] ?? []),
                'sortable' => collect($schema['columns'] ?? [])
                    ->filter(fn ($c) => is_object($c) && $c->isSortable())
                    ->map(fn ($c) => ['name' => $c->getName(), 'label' => $c->getLabel()])
                    ->values()
                    ->toArray(),
                'bulk_actions' => array_values(array_map(
                    fn ($a) => is_object($a) && method_exists($a, 'toArray') ? $a->toArray() : $a,
                    $schema['bulk_actions'] ?? []
                )),
                'settings' => array_merge([
                    'bulk_url' => null,
                    'query_prefix' => $schema['query_prefix'] ?? null,
                ], $schema['settings'] ?? []),
            ],
        ];
    }

    public function runBulkAction(BaseResource $resource, Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'bulk_action' => ['required', 'string'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required'],
        ]);

        $schema = $resource->schema();
        $actions = $schema['bulk_actions'] ?? [];

        foreach ($actions as $action) {
            if (!is_object($action) || !method_exists($action, 'getName')) {
                continue;
            }
            if ($action->getName() !== $validated['bulk_action']) {
                continue;
            }

            $action->execute(array_values($validated['ids']));

            $message = method_exists($action, 'getSuccessMessage')
                ? $action->getSuccessMessage()
                : 'Done.';

            Notify::success($message);

            return back();
        }

        abort(404, 'Bulk action not found.');
    }

    private function serializeTabs(TabCollection $tabs): array
    {
        return array_values(array_map(fn ($tab) => $tab->toArray(), $tabs->all()));
    }

    private function handleExport(BaseResource $resource, Request $request, array $schema): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $context = new QueryContext($resource->getModel()::query(), $request, $schema);
        $context = $this->pipeline->process($context);

        $columns = collect($schema['columns'] ?? [])->filter(
            fn ($c) => is_object($c) && $c->isExportable() && !$c->isHidden()
        );

        $headers = $columns->map(fn ($c) => $c->getLabel())->values()->toArray();
        $query = $context->query;

        $callback = function () use ($query, $columns, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($query->lazy(500) as $record) {
                $arr = $record->toArray();
                $row = $columns->map(function ($col) use ($arr) {
                    $value = data_get($arr, $col->getName());

                    return $col->transformValue($value, $arr);
                })->values()->toArray();
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $resource->getKey() . '.csv',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
