<?php

namespace App\AdminPanel\Schema\Layout;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasSchema;

class Form extends Component
{
    use HasSchema;

    protected string $action = '';
    protected string $method = 'POST';
    protected bool $bordered = false;

    protected function getType(): string
    {
        return 'form';
    }

    public function action(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function method(string $method): static
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Wrap the form in a bordered card surface (padding + border).
     * Off by default — use Card::make()->border() for section cards instead.
     */
    public function border(bool $bordered = true): static
    {
        $this->bordered = $bordered;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'action' => $this->action,
            'method' => $this->method,
            'bordered' => $this->bordered,
            'schema' => $this->serializeSchema(),
        ]);
    }
}
