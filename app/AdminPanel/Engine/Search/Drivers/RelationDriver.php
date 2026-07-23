<?php

namespace App\AdminPanel\Engine\Search\Drivers;

use App\AdminPanel\Engine\Contracts\SearchDriverContract;
use Illuminate\Database\Eloquent\Builder;

/**
 * Relation-based search driver.
 * Searches through a related model's column via whereHas.
 */
class RelationDriver implements SearchDriverContract
{
    public function apply(Builder $query, string $column, string $term, bool $orWhere = false, ?string $relation = null): Builder
    {
        $safe = '%' . addslashes($term) . '%';
        // column format: "relation.field" or use separate $relation param
        if ($relation) {
            $field = $column;
        } elseif (str_contains($column, '.')) {
            [$relation, $field] = explode('.', $column, 2);
        } else {
            // Can't resolve — fallback to LIKE on column
            $method = $orWhere ? 'orWhere' : 'where';
            return $query->$method($column, 'LIKE', $safe);
        }

        $method = $orWhere ? 'orWhereHas' : 'whereHas';
        return $query->$method($relation, fn($q) => $q->where($field, 'LIKE', $safe));
    }

    public function supports(string $strategy): bool
    {
        return $strategy === 'relation';
    }
}
