<?php

namespace App\AdminPanel\Tables\Tabs;

use App\AdminPanel\Engine\Contracts\TabContract;

class TabCollection
{
    /** @var TabContract[] */
    private array $tabs;

    public function __construct(array $tabs)
    {
        $this->tabs = $tabs;
    }

    public static function make(array $tabs): static
    {
        return new static($tabs);
    }

    /**
     * @return TabContract[]
     */
    public function all(): array
    {
        return array_values(
            array_filter($this->tabs, fn(TabContract $t) => $t->isVisible())
        );
    }

    public function find(string $value): ?TabContract
    {
        foreach ($this->tabs as $tab) {
            if ($tab->getValue() === $value)
                return $tab;
        }
        return null;
    }

    /**
     * The first tab value = "All" (no constraint)
     */
    public function defaultValue(): ?string
    {
        return $this->tabs[0]?->getValue() ?? null;
    }

    public function toArray(): array
    {
        return array_map(fn(TabContract $t) => $t->toArray(), $this->all());
    }
}
