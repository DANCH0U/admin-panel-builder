<?php

namespace App\AdminPanel\Schema\UI;

use App\AdminPanel\Schema\Component;

/**
 * Vertical spacer for schema layouts.
 *
 * Space::make()       // default size 4
 * Space::make(8)
 * Space::make()->size(6)
 */
class Space extends Component
{
    protected int $size = 4;

    protected function getType(): string
    {
        return 'space';
    }

    public static function make(mixed $size = null): static
    {
        $space = parent::make(null);

        if ($size !== null) {
            $space->size((int) $size);
        }

        return $space;
    }

    public function size(int $size): static
    {
        $this->size = max(0, $size);

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'size' => $this->size,
        ]);
    }
}
