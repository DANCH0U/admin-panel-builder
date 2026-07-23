<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;

class Checkbox extends Component
{
    protected function getType(): string
    {
        return 'checkbox';
    }

    public function toArray(): array
    {
        return parent::toArray();
    }
}
