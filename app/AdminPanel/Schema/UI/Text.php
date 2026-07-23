<?php

namespace App\AdminPanel\Schema\UI;

use App\AdminPanel\Schema\Component;

class Text extends Component
{
    protected string $content = '';
    protected string $variant = 'body';

    protected function getType(): string
    {
        return 'ui-text';
    }

    public static function make(mixed $content = null): static
    {
        $text = parent::make(null);

        return $content !== null ? $text->content((string) $content) : $text;
    }

    public function content(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function variant(string $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'content' => $this->content,
            'variant' => $this->variant,
        ]);
    }
}
