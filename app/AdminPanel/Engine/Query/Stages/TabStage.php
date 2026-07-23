<?php

namespace App\AdminPanel\Engine\Query\Stages;

use App\AdminPanel\Engine\Contracts\QueryStageContract;
use App\AdminPanel\Engine\Query\QueryContext;
use App\AdminPanel\Tables\Tabs\TabCollection;

/**
 * Stage 4 — Applies the active tab's query constraint.
 * The first tab in the collection = "All" = no constraint.
 */
class TabStage implements QueryStageContract
{
    public function handle(QueryContext $context, \Closure $next): QueryContext
    {
        $activeTab = $context->getActiveTab();

        /** @var TabCollection|null $tabs */
        $tabs = $context->schema['tabs'] ?? null;

        if ($activeTab && $tabs instanceof TabCollection) {
            $tab = $tabs->find($activeTab);

            if ($tab && $tab->getValue() !== $tabs->defaultValue()) {
                $tab->applyQuery($context->query);
            }
        }

        return $next($context);
    }
}
