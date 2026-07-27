<?php

namespace App\AdminPanel\Engine;

use App\AdminPanel\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Thin base class — resources only describe metadata.
 * All execution happens in DataGridEngine.
 */
abstract class BaseResource
{
    protected string $key = '';

    /** @var class-string<Model> */
    protected string $model = '';

    abstract public function schema(): array;

    public function getModel(): Model
    {
        $class = $this->model;

        if ($class === '' || !is_a($class, Model::class, true)) {
            throw new LogicException(sprintf(
                '%s::$model must be an Eloquent model class name.',
                static::class,
            ));
        }

        return new $class();
    }

    public function getKey(): string
    {
        return $this->key ?: class_basename(static::class);
    }

    /**
     * Document title for <Head> (browser tab).
     */
    public function title(): ?string
    {
        return null;
    }

    /**
     * Schema nodes rendered above the DataTable (headings, cards, action buttons, …).
     *
     * @return list<\App\AdminPanel\Schema\Component|array<string, mixed>>
     */
    public function header(): array
    {
        return [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSerializedHeader(): array
    {
        return array_values(array_map(
            fn (mixed $component) => is_object($component) && method_exists($component, 'toArray')
                ? $component->toArray()
                : $component,
            $this->header(),
        ));
    }

    /**
     * Props for Admin/ResourceIndex Inertia page.
     *
     * @param  array<string, mixed>  $resourceData  Output of DataGridEngine::handle()
     * @return array{resource: array<string, mixed>, title: ?string, header: list<array<string, mixed>>}
     */
    public function toIndexProps(array $resourceData): array
    {
        return [
            'resource' => $resourceData,
            'title' => $this->title(),
            'header' => $this->getSerializedHeader(),
        ];
    }

    /**
     * Transform raw model arrays to action-resolved rows for the frontend.
     */
    public function transform(array $items, array $actions): array
    {
        $result = [];

        foreach ($items as $item) {
            $row = $item;
            $row['table_actions'] = [];

            foreach ($actions as $action) {
                if (!$action instanceof Action) {
                    continue;
                }

                if ($resolved = $action->resolve($item)) {
                    $row['table_actions'][] = $resolved;
                }
            }

            $result[] = $row;
        }

        return $result;
    }
}
