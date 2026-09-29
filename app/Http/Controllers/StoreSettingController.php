<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use App\Repository\ShopLogoRepository;
use Illuminate\Http\Request;

/**
 * Shop-wide settings the admin owns. Today that is the bill header - the name,
 * address and registration numbers printed on every slip a customer keeps.
 * These used to be typed into three Blade files, where they drifted into two
 * different shops. The same name and logo brand the admin sidebar and sign-in
 * page, so a shop the system is sold to rebrands it from here.
 */
class StoreSettingController extends Controller
{
    public function __construct(private ShopLogoRepository $logoRepo)
    {
    }

    public function invoiceHeader()
    {
        return view('settings.invoice-header', [
            'header' => StoreSetting::invoiceHeader(),
            'brand' => StoreSetting::shopBrand(),
        ]);
    }

    public function updateInvoiceHeader(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:120'],
                'address' => ['nullable', 'string', 'max:160'],
                'pan' => ['nullable', 'string', 'max:30'],
                'phone' => ['nullable', 'string', 'max:40'],
                'footer_note' => ['nullable', 'string', 'max:160'],
                // No SVG: it is served from public/ and can carry script.
                'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            ], [
                'logo.uploaded' => 'The logo must be 1 MB or smaller.',
                'logo.max' => 'The logo must be 1 MB or smaller.',
            ]);

            $previousLogo = StoreSetting::invoiceHeader()['logo'];
            unset($data['logo']);

            if ($request->hasFile('logo')) {
                $data['logo'] = $this->logoRepo->storeImageFile($request->file('logo'));
            } elseif ($request->boolean('remove_logo')) {
                $data['logo'] = null;
            }

            try {
                StoreSetting::saveInvoiceHeader($data);
            } catch (\Exception $e) {
                // Do not leave the just-uploaded file behind if the setting never saved.
                if ($request->hasFile('logo')) {
                    $this->logoRepo->deleteImageFile($data['logo']);
                }
                throw $e;
            }

            if (array_key_exists('logo', $data) && $previousLogo !== $data['logo']) {
                $this->logoRepo->deleteImageFile($previousLogo);
            }

            return redirect()->route('admin.settings.invoiceHeader')
                ->with(['message' => 'Shop details updated.', 'type' => 'success']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }
}
