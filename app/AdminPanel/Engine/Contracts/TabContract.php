<?php

namespace App\AdminPanel\Engine\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface TabContract
{
    public function getValue(): string;
    public function getLabel(): string;
    public function applyQuery(Builder $query): Builder;
    public function resolveCount(Builder $baseQuery): int;
    public function isVisible(): bool;
    public function shouldShowCount(): bool;
    public function getBadgeColor(): string;
    public function toArray(): array;
}
