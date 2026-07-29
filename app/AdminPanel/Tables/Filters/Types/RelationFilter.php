<?php

namespace App\AdminPanel\Tables\Filters\Types;

use App\AdminPanel\Tables\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class RelationFilter extends AbstractFilter
{
    protected string $type = 'select';
    protected string $relation = '';
    protected array $options = [];

    public function relation(string $relation): static
    {
        $this->relation = $relation;
        return $this;
    }

    public function options(array|\Closure $options): static
    {
        $this->options = $options instanceof \Closure ? $options() : $options;
        return $this;
    }

    public function apply(Builder $query, mixed $value): void
    {
        $query->whereHas($this->relation, fn($q) => $q->where($this->column, $value));
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->options,
            'relation' => $this->relation,
        ]);
    }
}
