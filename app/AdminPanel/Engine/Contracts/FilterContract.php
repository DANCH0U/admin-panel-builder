<?php

namespace App\AdminPanel\Engine\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface FilterContract
{
    public function getKey(): string;
    public function getLabel(): string;
    public function getType(): string;
    public function validate(mixed $value): bool;
    public function transform(mixed $value): mixed;
    public function apply(Builder $query, mixed $value): void;
    public function toArray(): array;
}
