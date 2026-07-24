<?php

namespace App\AdminPanel\Engine;

abstract class BasePage
{
    abstract public function schema(): array;

    public function title(): ?string
    {
        return null;
    }

    /**
     * Return false to block access (403). Override in generated pages.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function authorizeOrFail(): static
    {
        if (! $this->authorize()) {
            abort(403, 'Unauthorized');
        }

        return $this;
    }

    public function toInertia(array $extra = []): array
    {
        $this->authorizeOrFail();

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
