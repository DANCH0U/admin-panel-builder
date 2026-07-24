<?php

namespace App\AdminPanel\Tables\Columns;

use App\Services\FileUploadService;

class ImageColumn extends AbstractColumn
{
    protected string $type = 'image';

    protected bool $rounded = false;

    public function rounded(bool $v = true): static
    {
        $this->rounded = $v;

        return $this;
    }

    public function transformValue(mixed $value, array $record): mixed
    {
        $v = parent::transformValue($value, $record);

        if (! is_string($v) || $v === '') {
            return $v;
        }

        if (
            str_starts_with($v, 'http://')
            || str_starts_with($v, 'https://')
            || str_starts_with($v, 'blob:')
            || str_starts_with($v, '/')
        ) {
            return $v;
        }

        return app(FileUploadService::class)->url($v) ?? $v;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['rounded' => $this->rounded]);
    }
}
