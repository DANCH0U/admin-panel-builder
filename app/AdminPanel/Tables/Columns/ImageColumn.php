<?php

namespace App\AdminPanel\Tables\Columns;

class ImageColumn extends AbstractColumn
{
    protected string $type = 'image';
    protected bool $rounded = false;

    public function rounded(bool $v = true): static
    {
        $this->rounded = $v;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['rounded' => $this->rounded]);
    }
}
