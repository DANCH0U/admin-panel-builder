<?php

namespace App\AdminPanel\Engine;

abstract class BasePage
{
    abstract public function schema(): array;

    public function title(): ?string
    {
        return null;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function toInertia(array $extra = []): array
    {
        return array_merge([
            'schema' => $this->getSerializedSchema(),
            'title' => $this->title(),
        ], $extra);
    }

    public function getSerializedSchema(): array
    {
        return array_map(
            fn (mixed $component) => is_object($component) && method_exists($component, 'toArray')
                ? $component->toArray()
                : $component,
            $this->schema(),
        );
    }
}
