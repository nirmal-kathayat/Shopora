<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\CartItem;
use App\Models\InventoryItem;
use App\Repository\CatalogueRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The signed-in customer's server-side cart. Every route needs a customer
 * token; a guest keeps their cart in the browser until they sign in.
 */
class CartController extends Controller
{
    private CatalogueRepository $catalogue;

    public function __construct(CatalogueRepository $catalogue)
    {
        $this->catalogue = $catalogue;
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['items' => $this->cartPayload($request->user()->id)]);
    }

    /** Add to the cart, incrementing if the product is already in it. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $add = $data['qty'] ?? 1;
        $item = CartItem::firstOrNew([
            'customer_id' => $request->user()->id,
            'inventory_item_id' => $data['product_id'],
        ]);
        $item->qty = $this->clampToStock($data['product_id'], (int) $item->qty + $add);
        $item->save();

        return response()->json(['items' => $this->cartPayload($request->user()->id)], 201);
    }

    /** Set an exact quantity; a quantity of 0 removes the line. */
    public function updateQty(Request $request, int $productId): JsonResponse
    {
        $data = $request->validate(['qty' => ['required', 'integer', 'min:0']]);

        $item = CartItem::where('customer_id', $request->user()->id)
            ->where('inventory_item_id', $productId)
            ->first();

        if ($item) {
            $qty = $this->clampToStock($productId, $data['qty']);
            $qty <= 0 ? $item->delete() : $item->update(['qty' => $qty]);
        }

        return response()->json(['items' => $this->cartPayload($request->user()->id)]);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        CartItem::where('customer_id', $request->user()->id)
            ->where('inventory_item_id', $productId)
            ->delete();

        return response()->json(['items' => $this->cartPayload($request->user()->id)]);
    }

    /**
     * Fold a guest's browser cart into the server cart on sign-in. Quantities
     * add to whatever is already there, capped at stock.
     */
    public function merge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['array'],
            'items.*.product_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        foreach (($data['items'] ?? []) as $row) {
            $item = CartItem::firstOrNew([
                'customer_id' => $request->user()->id,
                'inventory_item_id' => $row['product_id'],
            ]);
            $item->qty = $this->clampToStock($row['product_id'], (int) $item->qty + $row['qty']);
            $item->save();
        }

        return response()->json(['items' => $this->cartPayload($request->user()->id)]);
    }

    /** Never promise more units than the shop actually holds. */
    private function clampToStock(int $productId, int $qty): int
    {
        $product = $this->catalogue->findMany([$productId])->first();
        $stock = $product ? (int) $product->stock_qty : 0;

        return max(0, min($qty, $stock));
    }

    /**
     * The cart as { product, qty } lines, using the same product shape as the
     * catalogue so the storefront renders it with the same components.
     *
     * @return array<int, array<string, mixed>>
     */
    private function cartPayload(int $customerId): array
    {
        $quantities = CartItem::where('customer_id', $customerId)
            ->pluck('qty', 'inventory_item_id');

        if ($quantities->isEmpty()) {
            return [];
        }

        return $this->catalogue->findMany($quantities->keys()->all())
            ->map(fn ($product) => [
                'product' => (new ProductResource($product))->resolve(),
                'qty' => (int) $quantities[$product->id],
            ])
            ->values()
            ->all();
    }
}
