<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Repository\StockAlertRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The storefront's "Notify me when back in stock" button.
 *
 * Signed-in only, and deliberately so: being told means a notification in the
 * account, which needs an account to arrive in. Taking an email address from
 * a stranger here would be collecting addresses the shop has no configured
 * way to send to - a button that promises something it cannot do.
 */
class StockAlertController extends Controller
{
    public function __construct(private readonly StockAlertRepository $alerts)
    {
    }

    /**
     * Everything this customer is waiting on, as bare ids.
     *
     * One request draws every "Notify me" button on the page in the right
     * state, rather than one request per out-of-stock card.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(['product_ids' => $this->alerts->waitingFor($request->user())]);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        if (! InventoryItem::whereKey($id)->exists()) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $this->alerts->join($request->user(), $id);

        return response()->json([
            'waiting' => true,
            'message' => 'We will tell you as soon as this is back.',
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->alerts->leave($request->user(), $id);

        return response()->json(['waiting' => false]);
    }
}
