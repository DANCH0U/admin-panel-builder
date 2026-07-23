<?php

namespace App\AdminPanel\Tables\Filters;

use App\AdminPanel\Engine\Contracts\FilterContract;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractFilter implements FilterContract
{
    protected string $key;
    protected string $name;
    protected string $label = '';
    protected string $type = 'filter';
    protected ?string $column = null;
    protected array $rules = [];

    public function __construct(string $key)
    {
        $this->key = $key;
        $this->name = $key;
        $this->column = $key;
    }

    public static function make(string $key): static
    {
        return new static($key);
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function column(string $col): static
    {
        $this->column = $col;
        return $this;
    }

    public function rules(array $rules): static
    {
        $this->rules = $rules;
        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function validate(mixed $value): bool
    {
        if (empty($this->rules))
            return true;
        $validator = validator([$this->key => $value], [$this->key => $this->rules]);
        return !$validator->fails();
    }

    // Default: pass through unchanged; subclasses may override
    public function transform(mixed $value): mixed
    {
        return $value;
    }

    abstract public function apply(Builder $query, mixed $value): void;

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'name' => $this->name,
        ];
    }
}
