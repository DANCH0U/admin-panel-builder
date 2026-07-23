<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasFieldOptions;

class ListInput extends Component
{
    use HasFieldOptions;

    protected string $addLabel = 'Add item';

    protected ?int $max = null;

    protected ?int $min = null;

    protected function getType(): string
    {
        return 'list-input';
    }

    public function addLabel(string $label): static
    {
        $this->addLabel = $label;

        return $this;
    }

    public function max(int $max): static
    {
        $this->max = $max;

        return $this;
    }

    public function min(int $min): static
    {
        $this->min = $min;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), $this->fieldOptions(), [
            'addLabel' => $this->addLabel,
            'max' => $this->max,
            'min' => $this->min,
        ]);
    }
}
