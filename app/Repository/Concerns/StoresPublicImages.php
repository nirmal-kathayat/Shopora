<?php

namespace App\Repository\Concerns;

use Illuminate\Http\UploadedFile;

/**
 * Uploads that belong to a piece of storefront content. They live in
 * public/image alongside the inventory photos, named with a per-repository
 * prefix so it is obvious what a stray file came from.
 */
trait StoresPublicImages
{
    /** Prefix for filenames this repository writes. */
    abstract protected function imagePrefix(): string;

    public function storeImageFile(UploadedFile $file): string
    {
        $directory = public_path('image');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = $this->imagePrefix() . '_' . time() . '_' . uniqid() . '.' . $extension;
        $file->move($directory, $filename);

        return $filename;
    }

    public function deleteImageFile(?string $filename): void
    {
        if (empty($filename)) {
            return;
        }

        $path = public_path('image/' . ltrim($filename, '/'));
        if (is_file($path)) {
            unlink($path);
        }
    }
}
