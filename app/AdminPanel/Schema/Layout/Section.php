<?php

namespace App\AdminPanel\Schema\Layout;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasSchema;

class Section extends Component
{
    use HasSchema;

    protected string $description = '';
    protected bool $foldable = false;

    protected function getType(): string
    {
        return 'section';
    }

    public static function make(mixed $heading = null): static
    {
        $section = parent::make($heading);

        return $heading !== null ? $section->label((string) $heading) : $section;
    }

    public function description(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function foldable(bool $foldable = true): static
    {
        $this->foldable = $foldable;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'description' => $this->description,
            'foldable' => $this->foldable,
            'schema' => $this->serializeSchema(),
        ]);
    }
}
