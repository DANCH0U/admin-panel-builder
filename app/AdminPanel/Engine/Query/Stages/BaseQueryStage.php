<?php

namespace App\AdminPanel\Engine\Query\Stages;

use App\AdminPanel\Engine\Contracts\QueryStageContract;
use App\AdminPanel\Engine\Query\QueryContext;

/**
 * Stage 2 — Applies the resource's custom 'query' closure.
 */
class BaseQueryStage implements QueryStageContract
{
    public function handle(QueryContext $context, \Closure $next): QueryContext
    {
        $closure = $context->schema['query'] ?? null;

        if ($closure instanceof \Closure) {
            $closure($context->query);
        }

        return $next($context);
    }
}
