<?php

namespace App\AdminPanel\Tables\Filters\Types;

use App\AdminPanel\Tables\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class SelectFilter extends AbstractFilter
{
    protected string $type = 'select';
    protected array $options = [];

    public function options(array $options): static
    {
        $formatted = [];
        foreach ($options as $key => $value) {
            if (is_array($value) && isset($value['value'], $value['label'])) {
                $formatted[] = $value;
            } else {
                $formatted[] = ['value' => (string) $key, 'label' => $value];
            }
        }
        $this->options = $formatted;
        return $this;
    }

    public function apply(Builder $query, mixed $value): void
    {
        if ($value === '' || $value === null) {
            return;
        }

        // Support boolean filters sent as "1" / "0"
        if ($value === '1' || $value === '0' || $value === 1 || $value === 0 || is_bool($value)) {
            $query->where($this->column, filter_var($value, FILTER_VALIDATE_BOOLEAN) || $value === '1' || $value === 1);

            return;
        }

        $query->where($this->column, $value);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['options' => $this->options]);
    }
}
