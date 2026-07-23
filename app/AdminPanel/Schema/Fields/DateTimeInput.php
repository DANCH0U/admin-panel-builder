<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasFieldOptions;

class DateTimeInput extends Component
{
    use HasFieldOptions;

    protected bool $withTime = true;

    protected string $displayFormat = 'MMM D, YYYY HH:mm';

    protected function getType(): string
    {
        return 'datetime-input';
    }

    public function withTime(bool $withTime = true): static
    {
        $this->withTime = $withTime;

        return $this;
    }

    public function dateOnly(): static
    {
        return $this->withTime(false);
    }

    public function displayFormat(string $format): static
    {
        $this->displayFormat = $format;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), $this->fieldOptions(), [
            'withTime' => $this->withTime,
            'displayFormat' => $this->displayFormat,
        ]);
    }
}
