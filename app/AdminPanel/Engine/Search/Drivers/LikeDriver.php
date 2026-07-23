<?php

namespace App\AdminPanel\Engine\Search\Drivers;

use App\AdminPanel\Engine\Contracts\SearchDriverContract;
use Illuminate\Database\Eloquent\Builder;

class LikeDriver implements SearchDriverContract
{
    public function apply(Builder $query, string $column, string $term, bool $orWhere = false, ?string $relation = null): Builder
    {
        $safe = '%' . addslashes($term) . '%';
        $method = $orWhere ? 'orWhere' : 'where';
        return $query->$method($column, 'LIKE', $safe);
    }

    public function supports(string $strategy): bool
    {
        return $strategy === 'like';
    }
}
