<?php

namespace App\AdminPanel\Engine\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface ColumnContract
{
    public function getName(): string;
    public function getLabel(): string;
    public function getType(): string;
    public function isHidden(): bool;
    public function isSortable(): bool;
    public function isExportable(): bool;
    public function isToggleable(): bool;
    public function getEagerLoad(): ?string;
    public function transformValue(mixed $value, array $record): mixed;
    public function toArray(): array;
}
