<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasFieldOptions;

/**
 * Raw JSON code editor (textarea). Prefer JsonInput for structured blocks.
 */
class JsonCodeInput extends Component
{
    use HasFieldOptions;

    protected int $rows = 10;

    protected bool $pretty = true;

    protected function getType(): string
    {
        return 'json-code';
    }

    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function pretty(bool $pretty = true): static
    {
        $this->pretty = $pretty;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), $this->fieldOptions(), [
            'rows' => $this->rows,
            'pretty' => $this->pretty,
            'placeholder' => $this->placeholder ?: "{\n  \"key\": \"value\"\n}",
        ]);
    }
}
