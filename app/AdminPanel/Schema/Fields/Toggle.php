<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;

class Toggle extends Component
{
    protected mixed $trueValue = 1;
    protected mixed $falseValue = 0;

    protected function getType(): string
    {
        return 'toggle';
    }

    public function values(mixed $true, mixed $false): static
    {
        $this->trueValue = $true;
        $this->falseValue = $false;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'trueValue' => $this->trueValue,
            'falseValue' => $this->falseValue,
        ]);
    }
}
