<?php

namespace App\AdminPanel\Tables\Columns;

class BadgeColumn extends AbstractColumn
{
    protected string $type = 'badge';
    protected array $colors = [];

    /**
     * Map badge colors to values.
     * e.g. ['success' => 'active', 'warning' => 'draft']
     */
    public function colors(array $colors): static
    {
        $this->colors = $colors;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['colors' => $this->colors]);
    }
}
