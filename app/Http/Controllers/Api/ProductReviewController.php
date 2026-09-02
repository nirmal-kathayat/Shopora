<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductReviewResource;
use App\Models\InventoryItem;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    /** Public: the reviews and their summary for one product. */
    public function index(Request $request, int $id): JsonResponse
    {
        if (! InventoryItem::whereKey($id)->exists()) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $reviews = ProductReview::where('inventory_item_id', $id)
            ->with('customer:id,name')
            ->latest()
            ->get();

        return response()->json([
            'summary' => $this->summary($reviews),
            'reviews' => ProductReviewResource::collection($reviews),
        ]);
    }

    /** A signed-in customer leaves (or updates) their review. */
    public function store(Request $request, int $id): JsonResponse
    {
        if (! InventoryItem::whereKey($id)->exists()) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        // One review per customer per product - a second attempt is refused.
        $already = ProductReview::where('inventory_item_id', $id)
            ->where('customer_id', $request->user()->id)
            ->exists();
        if ($already) {
            return response()->json([
                'message' => 'You have already reviewed this product.',
            ], 422);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:1500'],
        ]);

        $review = ProductReview::create($data + [
            'inventory_item_id' => $id,
            'customer_id' => $request->user()->id,
        ])->load('customer:id,name');

        $reviews = ProductReview::where('inventory_item_id', $id)->with('customer:id,name')->latest()->get();

        return response()->json([
            'review' => new ProductReviewResource($review),
            'summary' => $this->summary($reviews),
        ], 201);
    }

    /**
     * Average, total, and how many gave each star - for the rating breakdown.
     *
     * @param  \Illuminate\Support\Collection<int, ProductReview>  $reviews
     */
    private function summary($reviews): array
    {
        $count = $reviews->count();
        $distribution = [];
        foreach (range(5, 1) as $star) {
            $distribution[$star] = $reviews->where('rating', $star)->count();
        }

        return [
            'average' => $count > 0 ? round($reviews->avg('rating'), 1) : null,
            'count' => $count,
            'distribution' => $distribution,
        ];
    }
}
