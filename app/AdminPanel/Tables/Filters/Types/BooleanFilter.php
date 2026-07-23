<?php

namespace App\AdminPanel\Tables\Filters\Types;

use App\AdminPanel\Tables\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class BooleanFilter extends AbstractFilter
{
    protected string $type = 'boolean';
    protected string $trueLabel = 'Yes';
    protected string $falseLabel = 'No';

    public function labels(string $trueLabel, string $falseLabel): static
    {
        $this->trueLabel = $trueLabel;
        $this->falseLabel = $falseLabel;
        return $this;
    }

    public function transform(mixed $value): mixed
    {
        if (is_bool($value))
            return $value;
        return in_array($value, ['1', 'true', true, 1], true);
    }

    public function apply(Builder $query, mixed $value): void
    {
        $query->where($this->column, (bool) $value);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'true_label' => $this->trueLabel,
            'false_label' => $this->falseLabel,
            'options' => [
                ['value' => '1', 'label' => $this->trueLabel],
                ['value' => '0', 'label' => $this->falseLabel],
            ],
        ]);
    }
}
