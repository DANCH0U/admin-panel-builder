<?php

namespace App\AdminPanel\Engine\Query\Stages;

use App\AdminPanel\Engine\Contracts\QueryStageContract;
use App\AdminPanel\Engine\Query\QueryContext;

/**
 * Stage 7 — Applies ORDER BY.
 * Only allows sorting by columns declared as sortable in the schema.
 * Defaults to created_at desc.
 */
class SortStage implements QueryStageContract
{
    public function handle(QueryContext $context, \Closure $next): QueryContext
    {
        $sortBy = $context->getSortBy();
        $sortOrder = $context->getSortOrder();

        // Build whitelist from schema columns
        $sortable = collect($context->schema['columns'] ?? [])
            ->filter(fn($col) => method_exists($col, 'isSortable') && $col->isSortable())
            ->map(fn($col) => $col->getName())
            ->values()
            ->toArray();

        // Check if requested sort column is in whitelist
        if ($sortBy && in_array($sortBy, $sortable, true)) {
            $context->query->orderBy($sortBy, $sortOrder);
        } else {
            // Default sort
            $default = $context->schema['default_sort'] ?? ['created_at', 'desc'];
            $context->query->orderBy($default[0], $default[1] ?? 'desc');
        }

        return $next($context);
    }
}
