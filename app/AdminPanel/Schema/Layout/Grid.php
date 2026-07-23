<?php

namespace App\AdminPanel\Schema\Layout;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasSchema;

class Grid extends Component
{
    use HasSchema;

    protected int $cols = 12;

    protected function getType(): string
    {
        return 'grid';
    }

    public static function make(mixed $columns = 12): static
    {
        return (new static())->columns((int) $columns);
    }

    public function columns(int $cols): static
    {
        $this->cols = $cols;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'columns' => $this->cols,
            'schema' => $this->serializeSchema(),
        ]);
    }
}
