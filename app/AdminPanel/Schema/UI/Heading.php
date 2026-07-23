<?php

namespace App\AdminPanel\Schema\UI;

use App\AdminPanel\Schema\Component;

class Heading extends Component
{
    protected string $content = '';
    protected int $level = 2;

    protected function getType(): string
    {
        return 'ui-heading';
    }

    public static function make(mixed $content = null): static
    {
        $heading = parent::make(null);

        return $content !== null ? $heading->content((string) $content) : $heading;
    }

    public function content(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function level(int $level): static
    {
        $this->level = $level;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'content' => $this->content,
            'level' => $this->level,
        ]);
    }
}
