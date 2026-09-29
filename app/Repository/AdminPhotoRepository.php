<?php

namespace App\Repository;

use App\Models\Admin;
use App\Repository\Concerns\StoresPublicImages;
use Illuminate\Http\UploadedFile;

/**
 * An admin's profile photo. Replacing one deletes the file it replaces, so an
 * account never leaves a trail of orphaned uploads behind.
 */
class AdminPhotoRepository
{
    use StoresPublicImages;

    public function replace(Admin $admin, UploadedFile $file): void
    {
        $previous = $admin->image;

        $admin->image = $this->storeImageFile($file);
        $admin->save();

        $this->deleteImageFile($previous);
    }

    public function remove(Admin $admin): void
    {
        $previous = $admin->image;

        $admin->image = null;
        $admin->save();

        $this->deleteImageFile($previous);
    }

    protected function imagePrefix(): string
    {
        return 'admin';
    }
}
