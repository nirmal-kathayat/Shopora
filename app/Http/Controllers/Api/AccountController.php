<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfilePhotoRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\ProductReviewResource;
use App\Models\Customer;
use App\Models\ProductReview;
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

    /**
     * Everything this customer has reviewed, newest first, each with enough of
     * the product to link back to it.
     */
    public function reviews(Request $request): JsonResponse
    {
        $reviews = ProductReview::where('customer_id', $request->user()->id)
            ->with(['customer:id,name', 'inventoryItem:id,title,image'])
            ->latest()
            ->get();

        return response()->json([
            'reviews' => $reviews->map(fn ($review) => [
                'review' => (new ProductReviewResource($review))->toArray($request),
                'product' => [
                    'id' => $review->inventory_item_id,
                    'name' => $review->inventoryItem?->title,
                    'image' => inventoryItemImageUrl($review->inventoryItem?->image),
                ],
            ])->values(),
        ]);
    }

    /** A customer may take their own review back down. */
    public function deleteReview(Request $request, int $id): JsonResponse
    {
        ProductReview::where('customer_id', $request->user()->id)
            ->whereKey($id)
            ->firstOrFail()
            ->delete();

        return $this->reviews($request);
    }

    private function profileResponse(Customer $customer): JsonResponse
    {
        return response()->json([
            'customer' => new CustomerResource($customer),
            'stats' => $this->stats($customer),
        ]);
    }

    /**
     * The counters on the account header. Every counter sale and storefront
     * order is a sales row against the customer, so orders counts those.
     */
    private function stats(Customer $customer): array
    {
        return [
            'orders' => $customer->sales()->count(),
            'wishlist' => $customer->wishlistItems()->count(),
        ];
    }
}
