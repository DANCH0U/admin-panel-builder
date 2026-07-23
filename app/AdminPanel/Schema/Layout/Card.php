<?php

namespace App\AdminPanel\Schema\Layout;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasSchema;

class Card extends Component
{
    use HasSchema;

    protected bool $bordered = false;

    protected function getType(): string
    {
        return 'card';
    }

    /**
     * Show card chrome (border + padding + surface). Off by default.
     */
    public function border(bool $bordered = true): static
    {
        $this->bordered = $bordered;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'bordered' => $this->bordered,
            'schema' => $this->serializeSchema(),
        ]);
    }
}
