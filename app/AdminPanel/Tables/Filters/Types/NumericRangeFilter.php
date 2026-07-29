<?php

namespace App\AdminPanel\Tables\Filters\Types;

use App\AdminPanel\Tables\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reads ?search[key][min] and ?search[key][max] — either side may be omitted.
 * min()/max() only bound the inputs shown in the filter panel.
 */
class NumericRangeFilter extends AbstractFilter
{
    protected string $type = 'numeric_range';
    protected ?float $minValue = null;
    protected ?float $maxValue = null;

    public function min(float $v): static
    {
        $this->minValue = $v;
        return $this;
    }

    public function max(float $v): static
    {
        $this->maxValue = $v;
        return $this;
    }

    public function apply(Builder $query, mixed $value): void
    {
        if (! is_array($value)) {
            return;
        }

        $min = isset($value['min']) && $value['min'] !== '' ? (float) $value['min'] : null;
        $max = isset($value['max']) && $value['max'] !== '' ? (float) $value['max'] : null;

        if ($min !== null)
            $query->where($this->column, '>=', $min);
        if ($max !== null)
            $query->where($this->column, '<=', $max);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'min' => $this->minValue,
            'max' => $this->maxValue,
        ]);
    }
}
