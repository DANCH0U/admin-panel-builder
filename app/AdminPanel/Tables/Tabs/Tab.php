<?php

namespace App\AdminPanel\Tables\Tabs;

use App\AdminPanel\Engine\Contracts\TabContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Tab implements TabContract
{
    private string $value;
    private string $label;
    private bool $visible = true;
    private bool $showCount = false;
    private string $badgeColor = 'default';
    private ?\Closure $queryFn = null;

    public function __construct(string $value, ?string $label = null)
    {
        $this->value = $value;
        $this->label = $label ?? Str::headline($value);
    }

    public static function make(string $value, ?string $label = null): static
    {
        return new static($value, $label);
    }

    // ── Fluent ──────────────────────────────────────────────────────
    public function query(\Closure $fn): static
    {
        $this->queryFn = $fn;
        return $this;
    }

    public function color(string $color): static
    {
        $this->badgeColor = $color;
        return $this;
    }

    public function hidden(bool $v = true): static
    {
        $this->visible = !$v;
        return $this;
    }

    public function hideCount(): static
    {
        $this->showCount = false;
        return $this;
    }

    public function showCount(bool $show = true): static
    {
        $this->showCount = $show;
        return $this;
    }

    // ── Contract getters ──────────────────────────────────────────────
    public function getValue(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function shouldShowCount(): bool
    {
        return $this->showCount;
    }

    public function getBadgeColor(): string
    {
        return $this->badgeColor;
    }

    public function applyQuery(Builder $query): Builder
    {
        if ($this->queryFn) {
            ($this->queryFn)($query);
        }
        return $query;
    }

    public function resolveCount(Builder $baseQuery): int
    {
        $query = clone $baseQuery;
        return $this->applyQuery($query)->count();
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'color' => $this->badgeColor,
            'badge_color' => $this->badgeColor,
            'show_count' => $this->showCount,
        ];
    }
}
