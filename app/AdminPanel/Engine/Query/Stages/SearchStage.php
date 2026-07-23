<?php

namespace App\AdminPanel\Engine\Query\Stages;

use App\AdminPanel\Engine\Contracts\QueryStageContract;
use App\AdminPanel\Engine\Query\QueryContext;
use App\AdminPanel\Engine\Search\SearchPipeline;

/**
 * Stage 5 — Applies ?q= global search via SearchPipeline.
 * Columns are searched in weight-descending order inside a single OR block.
 */
class SearchStage implements QueryStageContract
{
    public function __construct(
        private SearchPipeline $pipeline
    ) {}

    public function handle(QueryContext $context, \Closure $next): QueryContext
    {
        $q = $context->getSearchQuery();
        $columns = $context->schema['search_columns'] ?? [];

        if ($q && count($columns)) {
            // Sort highest weight first
            usort($columns, fn($a, $b) => $b->getWeight() <=> $a->getWeight());

            $context->query->where(function ($query) use ($q, $columns) {
                foreach ($columns as $col) {
                    $driver = $this->pipeline->resolveDriver($col->getStrategy());
                    $driver->apply($query, $col->getName(), $q, orWhere: true, relation: $col->getRelation());
                }
            });
        }

        return $next($context);
    }
}
