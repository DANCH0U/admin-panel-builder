<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasFieldOptions;

class TextInput extends Component
{
    use HasFieldOptions;

    protected string $inputType = 'text';

    protected function getType(): string
    {
        return 'text-input';
    }

    public function type(string $type): static
    {
        $this->inputType = $type;

        return $this;
    }

    public function email(): static
    {
        return $this->type('email');
    }

    public function password(): static
    {
        return $this->type('password');
    }

    public function url(): static
    {
        return $this->type('url');
    }

    public function tel(): static
    {
        return $this->type('tel');
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), $this->fieldOptions(), [
            'inputType' => $this->inputType,
            'props' => array_merge($this->props, ['type' => $this->inputType]),
        ]);
    }
}
