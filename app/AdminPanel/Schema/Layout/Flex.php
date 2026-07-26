<?php

namespace App\AdminPanel\Schema\Layout;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasSchema;

class Flex extends Component
{
    use HasSchema;

    protected string $direction = 'row';
    protected string $justify = 'start';
    protected string $align = 'center';
    protected int $gap = 4;
    protected bool $wrap = false;
    protected bool $sticky = false;

    protected function getType(): string
    {
        return 'flex';
    }

    public function direction(string $direction): static
    {
        $this->direction = $direction;
        return $this;
    }

    public function justify(string $justify): static
    {
        $this->justify = $justify;
        return $this;
    }

    public function align(string $align): static
    {
        $this->align = $align;
        return $this;
    }

    public function gap(int $gap): static
    {
        $this->gap = $gap;
        return $this;
    }

    public function wrap(bool $wrap = true): static
    {
        $this->wrap = $wrap;
        return $this;
    }

    /**
     * Floating action bar on mobile; normal flex layout from md up.
     */
    public function sticky(bool $sticky = true): static
    {
        $this->sticky = $sticky;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'direction' => $this->direction,
            'justify' => $this->justify,
            'align' => $this->align,
            'gap' => $this->gap,
            'wrap' => $this->wrap,
            'sticky' => $this->sticky,
            'schema' => $this->serializeSchema(),
        ]);
    }
}
