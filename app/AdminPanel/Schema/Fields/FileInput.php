<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasFieldOptions;

class FileInput extends Component
{
    use HasFieldOptions;

    protected bool $image = false;

    protected bool $multiple = false;

    protected string $accept = '';

    protected string $disk = 'public';

    protected string $directory = 'uploads';

    protected int $maxSizeKb = 4096;

    protected function getType(): string
    {
        return 'file-input';
    }

    public function image(bool $image = true): static
    {
        $this->image = $image;

        if ($image && $this->accept === '') {
            $this->accept = 'image/*';
        }

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function accept(string $accept): static
    {
        $this->accept = $accept;

        return $this;
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function directory(string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function maxSizeKb(int $kb): static
    {
        $this->maxSizeKb = $kb;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), $this->fieldOptions(), [
            'image' => $this->image,
            'multiple' => $this->multiple,
            'accept' => $this->accept,
            'disk' => $this->disk,
            'directory' => $this->directory,
            'maxSizeKb' => $this->maxSizeKb,
        ]);
    }
}
