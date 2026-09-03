<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfilePhotoRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Repository\CustomerPhotoRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The signed-in customer's own record - what the storefront account page
 * reads and writes. Everything here is scoped to $request->user(); a customer
 * can never name someone else's row.
 */
class AccountController extends Controller
{
    private CustomerPhotoRepository $photos;

    public function __construct(CustomerPhotoRepository $photos)
    {
        $this->photos = $photos;
    }

    public function show(Request $request): JsonResponse
    {
        return $this->profileResponse($request->user());
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $customer = $request->user();

        $customer->fill($request->validated());
        $customer->save();

        return $this->profileResponse($customer->refresh());
    }

    public function uploadPhoto(UpdateProfilePhotoRequest $request): JsonResponse
    {
        return $this->profileResponse(
            $this->photos->replace($request->user(), $request->file('image'))
        );
    }

    public function deletePhoto(Request $request): JsonResponse
    {
        return $this->profileResponse($this->photos->remove($request->user()));
    }

    private function profileResponse(Customer $customer): JsonResponse
    {
        return response()->json([
            'customer' => new CustomerResource($customer),
            'stats' => $this->stats($customer),
        ]);
    }

    /**
     * The counters on the account header. Orders are real - every counter sale
     * and storefront order is a sales row against the customer. There is no
     * wishlist table yet, so that one is honestly zero until there is.
     */
    private function stats(Customer $customer): array
    {
        return [
            'orders' => $customer->sales()->count(),
            'wishlist' => 0,
        ];
    }
}
