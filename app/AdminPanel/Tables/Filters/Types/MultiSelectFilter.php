<?php

namespace App\AdminPanel\Tables\Filters\Types;

use App\AdminPanel\Tables\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class MultiSelectFilter extends AbstractFilter
{
    protected string $type = 'multiselect';
    protected array $options = [];

    public function options(array $options): static
    {
        $formatted = [];
        foreach ($options as $key => $value) {
            $formatted[] = is_array($value) ? $value : ['value' => (string) $key, 'label' => $value];
        }
        $this->options = $formatted;
        return $this;
    }

    public function transform(mixed $value): mixed
    {
        return is_array($value) ? $value : explode(',', (string) $value);
    }

    public function apply(Builder $query, mixed $value): void
    {
        $query->whereIn($this->column, (array) $value);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['options' => $this->options]);
    }
}
