<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasFieldOptions;

class NumberInput extends Component
{
    use HasFieldOptions;

    protected ?float $min = null;

    protected ?float $max = null;

    protected ?float $step = 1;

    protected bool $controls = true;

    protected function getType(): string
    {
        return 'number-input';
    }

    public function min(float $min): static
    {
        $this->min = $min;

        return $this;
    }

    public function max(float $max): static
    {
        $this->max = $max;

        return $this;
    }

    public function step(float $step): static
    {
        $this->step = $step;

        return $this;
    }

    public function controls(bool $controls = true): static
    {
        $this->controls = $controls;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), $this->fieldOptions(), [
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
            'controls' => $this->controls,
        ]);
    }
}
