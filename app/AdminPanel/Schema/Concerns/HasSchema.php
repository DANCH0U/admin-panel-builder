<?php

namespace App\AdminPanel\Schema\Concerns;

use App\AdminPanel\Schema\Component;

trait HasSchema
{
    protected array $schema = [];

    public function schema(array $schema): static
    {
        $this->schema = $schema;
        return $this;
    }

    protected function serializeSchema(): array
    {
        return array_map(function ($component) {
            return $component instanceof Component ? $component->toArray() : $component;
        }, $this->schema);
    }
}
