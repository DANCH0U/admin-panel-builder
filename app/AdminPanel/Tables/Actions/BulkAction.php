<?php

namespace App\AdminPanel\Tables\Actions;

use Illuminate\Support\Str;

class BulkAction
{
    public string $name;
    public string $label;
    public string $icon = '';
    public string $type = 'default';
    public bool $requiresConfirmation = false;
    public ?string $confirmTitle = null;
    public ?string $confirmText = null;
    public ?string $confirmButton = null;
    public string $successMessage = 'Done.';

    /** @var \Closure|null */
    protected $action = null;

    protected function __construct(string $name)
    {
        $this->name = $name;
        $this->label = Str::headline($name);
    }

    public static function make(string $name): static
    {
        return new static($name);
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

    public function successMessage(string $message): static
    {
        $this->successMessage = $message;
        return $this;
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

    public function action(\Closure $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function delete(\Closure $action): static
    {
        return $this
            ->destructive()
            ->requiresConfirmation(
                'Delete selected?',
                'This will permanently delete the selected records.',
                'Delete',
            )
            ->successMessage('Selected records deleted.')
            ->action($action);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSuccessMessage(): string
    {
        return $this->successMessage;
    }

    public function execute(array $ids): mixed
    {
        if (!$this->action) {
            return null;
        }

        return ($this->action)($ids);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'type' => $this->type,
            'requiresConfirmation' => $this->requiresConfirmation,
            'confirmTitle' => $this->confirmTitle,
            'confirmText' => $this->confirmText,
            'confirmButton' => $this->confirmButton,
        ];
    }
}
