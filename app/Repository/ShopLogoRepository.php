<?php

namespace App\Repository;

use App\Repository\Concerns\StoresPublicImages;

/**
 * The shop's own logo, shown on the admin sidebar and sign-in page. Only the
 * file lives here; which file is current is kept in the bill header setting.
 */
class ShopLogoRepository
{
    use StoresPublicImages;

    protected function imagePrefix(): string
    {
        return 'logo';
    }
}
