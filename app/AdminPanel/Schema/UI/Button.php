<?php

namespace App\AdminPanel\Schema\UI;

use App\AdminPanel\Schema\Component;

class Button extends Component
{
    protected string $variant = 'primary';
    protected string $icon = '';
    protected string $type_attr = 'button';
    protected string $url = '';
    protected bool $is_back = false;
    protected bool $showOnBottomBar = false;

    protected function getType(): string
    {
        return 'ui-button';
    }

    public static function make(mixed $label = null): static
    {
        $button = parent::make($label);

        return $label !== null ? $button->label((string) $label) : $button;
    }

    public function url(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function back(): static
    {
        $this->is_back = true;
        return $this;
    }

    public function variant(string $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function type(string $type): static
    {
        $this->type_attr = $type;
        return $this;
    }

    public function submit(): static
    {
        return $this->type('submit');
    }

    /**
     * On mobile, move this button into the fixed bottom action bar (hidden inline).
     * Desktop keeps the button in its normal place.
     */
    public function showOnBottomBar(bool $show = true): static
    {
        $this->showOnBottomBar = $show;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'variant' => $this->variant,
            'icon' => $this->icon,
            'type_attr' => $this->type_attr,
            'url' => $this->url,
            'is_back' => $this->is_back,
            'showOnBottomBar' => $this->showOnBottomBar,
        ]);
    }
}
