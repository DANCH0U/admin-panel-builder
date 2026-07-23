<?php

namespace App\AdminPanel\Engine\Contracts;

use App\AdminPanel\Engine\Query\QueryContext;

interface QueryStageContract
{
    /**
     * Process the query context and pass to the next stage.
     */
    public function handle(QueryContext $context, \Closure $next): QueryContext;
}
