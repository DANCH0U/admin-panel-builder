<?php

namespace App\AdminPanel\Schema\UI;

use App\AdminPanel\Schema\Component;
use App\Services\FileUploadService;

/**
 * Read-only image block for view pages.
 *
 * Image::make($pathOrUrl)->label('Cover')->rounded();
 */
class Image extends Component
{
    protected ?string $src = null;

    protected bool $rounded = false;

    protected function getType(): string
    {
        return 'ui-image';
    }

    public static function make(mixed $src = null): static
    {
        $image = parent::make(null);

        return $src !== null ? $image->src((string) $src) : $image;
    }

    public function src(?string $src): static
    {
        $this->src = $src;

        return $this;
    }

    public function rounded(bool $rounded = true): static
    {
        $this->rounded = $rounded;

        return $this;
    }

    public function toArray(): array
    {
        $src = $this->src;

        if (is_string($src) && $src !== '') {
            $src = app(FileUploadService::class)->url($src) ?? $src;
        }

        return array_merge(parent::toArray(), [
            'src' => $src,
            'rounded' => $this->rounded,
        ]);
    }
}
