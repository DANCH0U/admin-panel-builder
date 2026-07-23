<?php

namespace App\AdminPanel\Tables\Actions;

use Illuminate\Support\Str;

class Action
{
    public string $name;
    public string $label;
    public string $icon = '';
    public string $type = 'default';  // primary, destructive, secondary
    // Front-end execution details
    public ?string $url = null;
    public ?string $api = null;
    public string $method = 'POST';  // usually POST or DELETE for api
    // Modals & Confirmations
    public bool $requiresConfirmation = false;
    public ?string $confirmTitle = null;
    public ?string $confirmText = null;
    public ?string $confirmButton = null;
    // Closures for dynamic resolution per-row
    /** @var \Closure|null */
    public $urlClosure;
    /** @var \Closure|null */
    public $apiClosure;
    /** @var \Closure|null */
    public $hiddenClosure;
    /** @var \Closure|null */
    public $disabledClosure;
    // Grouping
    public array $items = [];

    protected function __construct(string $name)
    {
        $this->name = $name;
        $this->label = Str::headline($name);
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public static function group(string $label, array $items): static
    {
        $action = new static(Str::slug($label));
        $action->label($label)->items($items);
        return $action;
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function destructive(): static
    {
        return $this->type('destructive');
    }

    public function primary(): static
    {
        return $this->type('primary');
    }

    public function requiresConfirmation(
        string $title = 'Are you sure?',
        string $text = 'This action cannot be undone.',
        string $button = 'Confirm'
    ): static {
        $this->requiresConfirmation = true;
        $this->confirmTitle = $title;
        $this->confirmText = $text;
        $this->confirmButton = $button;
        return $this;
    }

    public function url(string|\Closure $url): static
    {
        if ($url instanceof \Closure) {
            $this->urlClosure = clone $url;
        } else {
            $this->url = $url;
        }
        return $this;
    }

    public function api(string|\Closure $api, string $method = 'POST'): static
    {
        $this->method = $method;
        if ($api instanceof \Closure) {
            $this->apiClosure = clone $api;
        } else {
            $this->api = $api;
        }
        return $this;
    }

    public function hiddenIf(\Closure $condition): static
    {
        $this->hiddenClosure = clone $condition;
        return $this;
    }

    public function disabledIf(\Closure $condition): static
    {
        $this->disabledClosure = clone $condition;
        return $this;
    }

    /**
     * Helper to easily map to Laravel Policies/Gates
     */
    public function can(string $ability, string $modelClass = null): static
    {
        $this->hiddenClosure = function ($row) use ($ability, $modelClass) {
            $user = auth()->user();
            if (!$user)
                return true;  // hidden if not logged in

            // if we have a model class string, check policy against the class string
            // otherwise check against the instantiated row
            $target = $modelClass ?? $row;
            return !$user->can($ability, $target);
        };
        return $this;
    }

    public function items(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    public function delete(string|\Closure $url): static
    {
        return $this
            ->api($url, 'DELETE')
            ->destructive()
            ->requiresConfirmation(
                'Delete record?',
                'This action cannot be undone.',
                'Delete',
            );
    }

    /**
     * Resolve closures and return a clean array for the frontend
     */
    public function resolve(object|array $row): ?array
    {
        // Check visibility first
        if ($this->hiddenClosure && ($this->hiddenClosure)($row)) {
            return null;
        }

        $resolved = [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'type' => $this->type,
            'url' => $this->urlClosure ? ($this->urlClosure)($row) : $this->url,
            'api' => $this->apiClosure ? ($this->apiClosure)($row) : $this->api,
            'method' => $this->method,
            'requiresConfirmation' => $this->requiresConfirmation,
            'confirmTitle' => $this->confirmTitle,
            'confirmText' => $this->confirmText,
            'confirmButton' => $this->confirmButton,
            'disabled' => $this->disabledClosure ? ($this->disabledClosure)($row) : false,
            'items' => [],
        ];

        // Resolve children recursively
        foreach ($this->items as $child) {
            if ($child instanceof self) {
                if ($resolvedChild = $child->resolve($row)) {
                    $resolved['items'][] = $resolvedChild;
                }
            } else {
                // Legacy support if array was passed
                $resolved['items'][] = $child;
            }
        }

        return $resolved;
    }
}
