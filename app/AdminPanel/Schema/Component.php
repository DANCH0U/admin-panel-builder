<?php

namespace App\AdminPanel\Schema;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

abstract class Component implements Arrayable, JsonSerializable
{
    protected string $type;
    protected string $label = '';
    protected array $props = [];
    protected ?string $width = null;
    protected ?int $columnSpan = null;
    protected string $helpText = '';

    public function __construct(protected ?string $name = null)
    {
        $this->type = $this->getType();
    }

    public static function make(mixed $name = null): static
    {
        return new static($name);
    }

    abstract protected function getType(): string;

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function props(array $props): static
    {
        $this->props = array_merge($this->props, $props);
        return $this;
    }

    public function width(string $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function columnSpan(int $span): static
    {
        $this->columnSpan = $span;
        return $this;
    }

    public function helpText(string $text): static
    {
        $this->helpText = $text;
        return $this;
    }

    public function visibleWhen(string $field, $value, string $operator = '='): static
    {
        $this->props['visibleWhen'] = [
            'field' => $field,
            'value' => $value,
            'operator' => $operator,
        ];
        return $this;
    }

    public function disabledWhen(string $field, $value, string $operator = '='): static
    {
        $this->props['disabledWhen'] = [
            'field' => $field,
            'value' => $value,
            'operator' => $operator,
        ];
        return $this;
    }

    public function toArray(): array
    {
        return array_merge($this->props, [
            'type' => $this->type,
            'name' => $this->name,
            'label' => $this->label,
            'width' => $this->width,
            'column_span' => $this->columnSpan,
            'helpText' => $this->helpText,
        ]);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
