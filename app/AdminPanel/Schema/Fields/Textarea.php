<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;

class Textarea extends Component
{
    protected int $rows = 3;
    protected string $placeholder = '';
    protected bool $required = false;

    protected function getType(): string
    {
        return 'textarea';
    }

    public function rows(int $rows): static
    {
        $this->rows = $rows;
        return $this;
    }

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

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'rows' => $this->rows,
            'placeholder' => $this->placeholder,
            'required' => $this->required,
        ]);
    }
}
