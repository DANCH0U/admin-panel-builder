<?php

namespace App\AdminPanel\Engine\Search\Drivers;

use App\AdminPanel\Engine\Contracts\SearchDriverContract;
use Illuminate\Database\Eloquent\Builder;

/**
 * MySQL FULLTEXT search driver.
 * Requires: ALTER TABLE ... ADD FULLTEXT(column1, column2)
 * Falls back to LIKE if the term is too short (< 3 chars).
 */
class FullTextDriver implements SearchDriverContract
{
    public function apply(Builder $query, string $column, string $term, bool $orWhere = false, ?string $relation = null): Builder
    {
        if (mb_strlen($term) < 3) {
            // Fallback to LIKE for short terms
            $safe = '%' . addslashes($term) . '%';
            $method = $orWhere ? 'orWhere' : 'where';
            return $query->$method($column, 'LIKE', $safe);
        }

        $method = $orWhere ? 'orWhereRaw' : 'whereRaw';
        return $query->$method("MATCH({$column}) AGAINST(? IN BOOLEAN MODE)", [$term . '*']);
    }

    public function supports(string $strategy): bool
    {
        return $strategy === 'fulltext';
    }
}
