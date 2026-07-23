<?php

namespace App\AdminPanel\Schema\Layout;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasSchema;

class Tab extends Component
{
    use HasSchema;

    protected ?string $icon = null;

    protected function getType(): string
    {
        return 'tab';
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'icon' => $this->icon,
            'schema' => $this->serializeSchema(),
        ]);
    }
}
