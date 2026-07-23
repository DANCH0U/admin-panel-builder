<?php

namespace App\AdminPanel\Tables\Columns;

class BooleanColumn extends AbstractColumn
{
    protected string $type = 'boolean';
    protected ?string $trueLabel = null;
    protected ?string $falseLabel = null;

    public function labels(string $true, string $false): static
    {
        $this->trueLabel = $true;
        $this->falseLabel = $false;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'true_label' => $this->trueLabel,
            'false_label' => $this->falseLabel,
        ]);
    }
}
