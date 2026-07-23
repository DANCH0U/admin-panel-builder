<?php

namespace App\AdminPanel\Schema\Concerns;

trait HasFieldOptions
{
    protected string $placeholder = '';

    protected bool $required = false;

    protected bool $disabled = false;

    protected bool $readonly = false;

    protected string $hint = '';

    protected string $size = 'md';

    protected mixed $default = null;

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function readonly(bool $readonly = true): static
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function hint(string $hint): static
    {
        $this->hint = $hint;

        return $this;
    }

    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    protected function fieldOptions(): array
    {
        return array_filter([
            'placeholder' => $this->placeholder ?: null,
            'required' => $this->required ?: null,
            'disabled' => $this->disabled ?: null,
            'readonly' => $this->readonly ?: null,
            'hint' => $this->hint ?: null,
            'size' => $this->size !== 'md' ? $this->size : null,
            'default' => $this->default,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
