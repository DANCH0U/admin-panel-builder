<?php

namespace App\AdminPanel\Menu;

class MenuItem implements \JsonSerializable
{
    public string $title = 'Menu Item';

    public ?string $key;

    public ?string $icon = 'heroicons:minus-16-solid';

    public ?string $url = null;

    public ?string $type = 'tab';

    public ?array $children = [];

    /** @var array{value: string, type: string, color?: string}|null */
    public ?array $suffix = null;

    public bool $disabled = false;

    public function __construct(string $key)
    {
        $this->title = $key;
        $this->key = $key;
    }

    public static function make(string $key = 'menu-key'): static
    {
        return new static($key);
    }

    public static function label(string $key, ?string $title = null): static
    {
        $item = static::make($key)->labelType();

        if ($title !== null) {
            $item->title($title);
        }

        return $item;
    }

    public static function link(string $key, string $url, ?string $title = null): static
    {
        $item = static::make($key)->url($url)->type('tab');

        if ($title !== null) {
            $item->title($title);
        }

        return $item;
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function labelType(): static
    {
        return $this->type('label');
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function children(array $children): static
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Trailing badge or icon in the sidebar.
     *
     * MenuItem::link(...)->suffix(value: '10', type: 'badge', color: 'danger')
     * MenuItem::link(...)->suffix(value: 'heroicons:home', type: 'icon', color: 'danger')
     */
    public function suffix(string $value, string $type = 'badge', ?string $color = null): static
    {
        $this->suffix = array_filter([
            'value' => $value,
            'type' => $type,
            'color' => $color,
        ], fn ($v) => $v !== null && $v !== '');

        return $this;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array_filter(
            get_object_vars($this),
            fn ($value) => $value !== null && $value !== [],
        );
    }
}
