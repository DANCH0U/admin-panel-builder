<?php

namespace App\AdminPanel\Menu;

class PanelMenu
{
    public array $menu = [];

    public bool $load_default = false;

    public function __construct() {}

    public static function make(): static
    {
        return new static();
    }

    public function default(bool $value = true): static
    {
        $this->load_default = $value;

        return $this;
    }

    public function items(array $items): static
    {
        array_push($this->menu, ...$items);

        return $this;
    }

    public function item(MenuItem $item): static
    {
        $this->menu[] = $item;

        return $this;
    }

    public function section(string $key, array $items, ?string $title = null): static
    {
        return $this
            ->item(MenuItem::label($key, $title))
            ->items($items);
    }

    public function admin(): static
    {
        return $this
            ->section('builder', [
                MenuItem::link('tests', admin_path('tests'))
                    ->icon('heroicons:beaker')
                    ->title('Tests'),
            ])
            ->section('settings', [
                MenuItem::link('panel_settings', admin_path('settings'))
                    ->icon('heroicons:cog-6-tooth'),
            ]);
    }

    public function build(): array
    {
        if ($this->load_default) {
            $menu = [
                MenuItem::make('overview')->labelType(),
                MenuItem::link('dashboard', admin_path())
                    ->icon('heroicons:home'),
                ...($this->menu ?? []),
            ];
        } else {
            $menu = $this->menu;
        }

        return $menu;
    }
}
