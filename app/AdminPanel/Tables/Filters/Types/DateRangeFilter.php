<?php

namespace App\AdminPanel\Tables\Filters\Types;

use App\AdminPanel\Tables\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter extends AbstractFilter
{
    protected string $type = 'date_range';

    public function apply(Builder $query, mixed $value): void
    {
        // value = ['from' => '2024-01-01', 'to' => '2024-12-31']
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

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'from_key' => $this->key . '_from',
            'to_key' => $this->key . '_to',
        ]);
    }
}
