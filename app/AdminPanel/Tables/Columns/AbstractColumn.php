<?php

namespace App\AdminPanel\Tables\Columns;

use App\AdminPanel\Engine\Contracts\ColumnContract;

abstract class AbstractColumn implements ColumnContract
{
    protected string $name;
    protected string $type = 'text';
    protected string $label = '';
    protected bool $hidden = false;
    protected bool $sortable = false;
    protected bool $toggleable = false;
    protected bool $exportable = true;
    protected ?string $eagerLoad = null;
    protected ?\Closure $transformer = null;
    protected ?string $prefixText = null;
    protected ?string $suffixText = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    // ── Fluent builders ───────────────────────────────────────────────
    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function hidden(bool $v = true): static
    {
        $this->hidden = $v;
        return $this;
    }

    public function sortable(bool $v = true): static
    {
        $this->sortable = $v;
        return $this;
    }

    public function toggleable(bool $v = true): static
    {
        $this->toggleable = $v;
        return $this;
    }

    public function exportable(bool $v = true): static
    {
        $this->exportable = $v;
        return $this;
    }

    public function using(string $rel): static
    {
        $this->eagerLoad = $rel;
        return $this;
    }

    /** Alias of using() — eager-load a BelongsTo / HasOne / etc. for dotted columns. */
    public function relationship(string $relation): static
    {
        return $this->using($relation);
    }

    public function prefixText(string $t): static
    {
        $this->prefixText = $t;
        return $this;
    }

    public function suffixText(string $t): static
    {
        $this->suffixText = $t;
        return $this;
    }

    public function transform(\Closure $fn): static
    {
        $this->transformer = $fn;
        return $this;
    }

    // ── Contract getters ──────────────────────────────────────────────
    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isToggleable(): bool
    {
        return $this->toggleable;
    }

    public function isExportable(): bool
    {
        return $this->exportable;
    }

    public function getEagerLoad(): ?string
    {
        return $this->eagerLoad;
    }

    public function transformValue(mixed $value, array $record): mixed
    {
        $v = $this->transformer ? ($this->transformer)($value, $record) : $value;

        if ($this->prefixText && $v !== null)
            $v = $this->prefixText . $v;
        if ($this->suffixText && $v !== null)
            $v = $v . $this->suffixText;

        return $v;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'hidden' => $this->hidden,
            'sortable' => $this->sortable,
            'toggleable' => $this->toggleable,
            'exportable' => $this->exportable,
        ];
    }
}
