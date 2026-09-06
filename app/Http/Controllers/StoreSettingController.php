<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\Request;

/**
 * Shop-wide settings the admin owns. Today that is the bill header - the name,
 * address and registration numbers printed on every slip a customer keeps.
 * These used to be typed into three Blade files, where they drifted into two
 * different shops.
 */
class StoreSettingController extends Controller
{
    public function invoiceHeader()
    {
        return view('settings.invoice-header', [
            'header' => StoreSetting::invoiceHeader(),
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
            ]);

            StoreSetting::saveInvoiceHeader($data);

            return redirect()->route('admin.settings.invoiceHeader')
                ->with(['message' => 'Bill header updated.', 'type' => 'success']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }
}
