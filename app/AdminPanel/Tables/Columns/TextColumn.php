<?php

namespace App\AdminPanel\Tables\Columns;

class TextColumn extends AbstractColumn
{
    protected string $type = 'text';
    protected ?int $maxLength = null;

    public function maxLength(int $length): static
    {
        $this->maxLength = $length;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'max_length' => $this->maxLength,
        ]);
    }
}
