<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;

class Select extends Component
{
    protected array $options = [];
    protected bool $searchable = false;
    protected string $placeholder = '';
    protected bool $required = false;

    protected function getType(): string
    {
        return 'select-input';
    }

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function optionsApi(string $url, ?string $dependsOn = null): static
    {
        $this->props['api'] = $url;
        if ($dependsOn) {
            $this->props['dependsOn'] = $dependsOn;
        }
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
            'options' => $this->options,
            'searchable' => $this->searchable,
            'placeholder' => $this->placeholder,
            'required' => $this->required,
        ]);
    }
}
