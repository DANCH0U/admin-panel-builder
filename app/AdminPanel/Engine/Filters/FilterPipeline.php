<?php

namespace App\AdminPanel\Engine\Filters;

use App\AdminPanel\Engine\Contracts\FilterContract;
use Illuminate\Database\Eloquent\Builder;

/**
 * Validates, transforms, and applies all active filters to the query.
 * Open/Closed — add new filter types without touching this class.
 */
class FilterPipeline
{
    /**
     * @param FilterContract[] $filters
     * @param array            $input    Validated request input map key => value
     */
    public function apply(Builder $query, array $filters, array $input): Builder
    {
        foreach ($filters as $filter) {
            $raw = $input[$filter->getKey()] ?? null;

            if ($raw === null || $raw === '' || $raw === []) {
                continue;
            }

            $value = $filter->transform($raw);

            if (!$filter->validate($value)) {
                continue;
            }

            $filter->apply($query, $value);
        }

        return $query;
    }
}
