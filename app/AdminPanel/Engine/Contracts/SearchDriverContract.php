<?php

namespace App\AdminPanel\Engine\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface SearchDriverContract
{
    /**
     * Apply a search term to the given query.
     * Use orWhere = true when combining multiple driver calls inside a single WHERE block.
     */
    public function apply(
        Builder $query,
        string $column,
        string $term,
        bool $orWhere = false,
        ?string $relation = null
    ): Builder;

    public function supports(string $strategy): bool;
}
