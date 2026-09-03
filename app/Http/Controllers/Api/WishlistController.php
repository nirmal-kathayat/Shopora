<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\WishlistItem;
use App\Repository\CatalogueRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The signed-in customer's saved products. Guests have no wishlist - the
 * storefront asks them to sign in rather than keeping one in the browser,
 * because a wishlist is only useful if it follows you between devices.
 */
class WishlistController extends Controller
{
    private CatalogueRepository $catalogue;

    public function __construct(CatalogueRepository $catalogue)
    {
        $this->catalogue = $catalogue;
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['items' => $this->wishlistPayload($request->user()->id)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:inventory_items,id'],
        ]);

        // firstOrCreate, so saving something twice is not an error.
        WishlistItem::firstOrCreate([
            'customer_id' => $request->user()->id,
            'inventory_item_id' => $data['product_id'],
        ]);

        return response()->json(['items' => $this->wishlistPayload($request->user()->id)], 201);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        WishlistItem::where('customer_id', $request->user()->id)
            ->where('inventory_item_id', $productId)
            ->delete();

        return response()->json(['items' => $this->wishlistPayload($request->user()->id)]);
    }

    /**
     * The saved products in the same shape as the catalogue, so the storefront
     * renders them with the components it already has. Newest save first.
     *
     * @return array<int, array<string, mixed>>
     */
    private function wishlistPayload(int $customerId): array
    {
        $order = WishlistItem::where('customer_id', $customerId)
            ->orderByDesc('id')
            ->pluck('id', 'inventory_item_id');

        if ($order->isEmpty()) {
            return [];
        }

        return $this->catalogue->findMany($order->keys()->all())
            ->sortByDesc(fn ($product) => $order[$product->id])
            ->map(fn ($product) => (new ProductResource($product))->resolve())
            ->values()
            ->all();
    }
}
