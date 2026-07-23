<?php

namespace App\AdminPanel\Engine\Query\Stages;

use App\AdminPanel\Engine\Contracts\QueryStageContract;
use App\AdminPanel\Engine\Filters\FilterPipeline;
use App\AdminPanel\Engine\Query\QueryContext;

/**
 * Stage 6 — Applies all advanced filters from ?search[key]=value.
 * Delegates to FilterPipeline which validates, transforms, and applies each filter.
 */
class FilterStage implements QueryStageContract
{
    public function __construct(
        private FilterPipeline $pipeline
    ) {}

    public function handle(QueryContext $context, \Closure $next): QueryContext
    {
        $filters = $context->schema['filters'] ?? [];
        $input = $context->getFilters();

        if (!empty($filters) && !empty($input)) {
            $this->pipeline->apply($context->query, $filters, $input);
        }

        return $next($context);
    }
}
