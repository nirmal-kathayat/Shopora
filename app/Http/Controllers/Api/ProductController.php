<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Repository\CatalogueRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The public catalogue. No token: this is what every visitor browses.
 */
class ProductController extends Controller
{
    private CatalogueRepository $products;

    public function __construct(CatalogueRepository $products)
    {
        $this->products = $products;
    }

    public function index(Request $request): JsonResponse
    {
        // A category that does not exist is a 404, so the storefront can show a
        // "not found" page rather than an empty grid that looks like a bug.
        $category = $request->query('category');
        if (filled($category) && ! $this->products->categoryExists((string) $category)) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $page = $this->products->paginate([
            'category' => $category,
            'q' => $request->query('q'),
            'sort' => $request->query('sort'),
            'availability' => $this->splitList($request->query('availability')),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'per_page' => $request->query('per_page'),
        ]);

        return response()->json([
            'data' => ProductResource::collection($page->items()),
            'meta' => [
                'total' => $page->total(),
                'per_page' => $page->perPage(),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
            ],
        ]);
    }

    /** "in,low" -> ['in', 'low']; anything empty -> []. */
    private function splitList($value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
