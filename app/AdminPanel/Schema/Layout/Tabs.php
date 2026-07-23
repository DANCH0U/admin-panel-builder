<?php

namespace App\AdminPanel\Schema\Layout;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasSchema;

class Tabs extends Component
{
    use HasSchema;

    protected function getType(): string
    {
        return 'tabs';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'schema' => $this->serializeSchema(),
        ]);
    }
}
