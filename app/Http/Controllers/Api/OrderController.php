<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Models\Sales;
use App\Repository\OrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The signed-in customer's orders. A storefront order is stored as a sale, so
 * the shop's stock, invoices and reports see it the same way they see anything
 * rung up at the counter - it only carries a status and an address on top.
 */
class OrderController extends Controller
{
    public function __construct(private readonly OrderRepository $orders)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Sales::storefront()
            ->where('customer_id', $request->user()->id)
            // Orders mid-payment are not shown until the money clears.
            ->where('status', '!=', 'pending_payment')
            ->with(['products.inventoryItem:id,title,image'])
            ->latest('id')
            ->get();

        return response()->json(['orders' => OrderResource::collection($orders)]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = Sales::storefront()
            ->where('customer_id', $request->user()->id)
            ->with(['products.inventoryItem:id,title,image'])
            ->findOrFail($id);

        return response()->json(['order' => new OrderResource($order)]);
    }

    /**
     * Place the cart as a cash-on-delivery order. Online payment goes through
     * PaymentController instead, which holds the order until the money lands.
     */
    public function store(Request $request): JsonResponse
    {
        $customer = $request->user();

        $data = $request->validate([
            'address_id' => ['required', 'integer'],
        ]);

        $address = $customer->addresses()->find($data['address_id']);
        if (! $address) {
            throw ValidationException::withMessages([
                'address_id' => 'Choose a delivery address first.',
            ]);
        }

        $result = $this->orders->place($customer, $address, 'cod', 'placed');

        // The cart has become the order.
        CartItem::where('customer_id', $customer->id)->delete();

        return response()->json([
            'order' => new OrderResource($result['order']->load('products.inventoryItem:id,title,image')),
        ], 201);
    }

    /**
     * Call it off while the shop has not shipped it. The units the order was
     * holding go back to stock with it.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = Sales::storefront()
            ->where('customer_id', $request->user()->id)
            ->findOrFail($id);

        if (! in_array($order->status, ['placed', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'status' => 'This order can no longer be cancelled. Please call the shop.',
            ]);
        }

        DB::transaction(function () use ($order) {
            $this->orders->releaseStock($order);
            $order->update(['status' => 'cancelled']);
        });

        return response()->json([
            'order' => new OrderResource($order->fresh()->load('products.inventoryItem:id,title,image')),
        ]);
    }
}
