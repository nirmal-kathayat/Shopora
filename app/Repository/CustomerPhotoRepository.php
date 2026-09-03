<?php

namespace App\Repository;

use App\Models\Customer;
use App\Repository\Concerns\StoresPublicImages;
use Illuminate\Http\UploadedFile;

/**
 * The customer's profile photo. Replacing one deletes the file it replaces,
 * so an account never leaves a trail of orphaned uploads behind.
 */
class CustomerPhotoRepository
{
    use StoresPublicImages;

    public function replace(Customer $customer, UploadedFile $file): Customer
    {
        $previous = $customer->image;

        $customer->image = $this->storeImageFile($file);
        $customer->save();

        $this->deleteImageFile($previous);

        return $customer->refresh();
    }

    public function remove(Customer $customer): Customer
    {
        $previous = $customer->image;

        $customer->image = null;
        $customer->save();

        $this->deleteImageFile($previous);

        return $customer->refresh();
    }

    protected function imagePrefix(): string
    {
        return 'customer';
    }
}
