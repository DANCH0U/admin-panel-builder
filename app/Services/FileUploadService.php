<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function upload(
        UploadedFile $file,
        string $directory = 'uploads',
        string $disk = 'public',
    ): string {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs(trim($directory, '/'), $filename, $disk);
    }

    public function delete(?string $path, string $disk = 'public'): bool
    {
        if (! $path) {
            return false;
        }

        $path = ltrim($path, '/');

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    public function url(?string $path, string $disk = 'public'): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk($disk)->url(ltrim($path, '/'));
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    public function uploadMultiple(array $files, string $directory = 'uploads', string $disk = 'public'): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->upload($file, $directory, $disk);
            }
        }

        return $paths;
    }
}
