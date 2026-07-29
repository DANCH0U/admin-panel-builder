<?php

namespace App\AdminPanel\Tables\Filters\Types;

use App\AdminPanel\Tables\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reads ?search[key][from] and ?search[key][to] — either side may be omitted.
 */
class DateRangeFilter extends AbstractFilter
{
    protected string $type = 'date_range';

    public function apply(Builder $query, mixed $value): void
    {
        if (! is_array($value)) {
            return;
        }

        $from = $value['from'] ?? null;
        $to = $value['to'] ?? null;

        if ($from && $to) {
            $query->whereBetween($this->column, [$from, $to]);
        } elseif ($from) {
            $query->whereDate($this->column, '>=', $from);
        } elseif ($to) {
            $query->whereDate($this->column, '<=', $to);
        }
    }
}
