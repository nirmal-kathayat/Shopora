<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Models\InventoryStock;
use App\Models\Sales;
use App\Models\SalesProduct;
use App\Repository\CatalogueRepository;
use NepaliDate\Facades\NepaliDate;
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
    /** Free delivery from this subtotal up; below it, the flat fee applies. */
    private const FREE_DELIVERY_FROM = 2000;

    private const DELIVERY_FEE = 100;

    private CatalogueRepository $catalogue;

    public function __construct(CatalogueRepository $catalogue)
    {
        $this->catalogue = $catalogue;
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Sales::storefront()
            ->where('customer_id', $request->user()->id)
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
     * Place the cart as an order. Prices and stock come from the database, not
     * from the browser - the only thing the customer chooses here is where it
     * goes.
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

        $quantities = CartItem::where('customer_id', $customer->id)->pluck('qty', 'inventory_item_id');
        if ($quantities->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        $products = $this->catalogue->findMany($quantities->keys()->all())->keyBy('id');

        $subtotal = 0.0;

        // Stock is checked before anything is written, and the price comes off
        // the product rather than the browser.
        foreach ($quantities as $productId => $qty) {
            $product = $products->get($productId);

            if (! $product) {
                throw ValidationException::withMessages([
                    'cart' => 'One of the products in your cart is no longer available.',
                ]);
            }

            if ((int) $product->stock_qty < $qty) {
                throw ValidationException::withMessages([
                    'cart' => "{$product->title} does not have enough stock left.",
                ]);
            }

            $subtotal += $qty * (float) $product->price_per_unit;
        }
        $deliveryFee = $subtotal >= self::FREE_DELIVERY_FROM ? 0 : self::DELIVERY_FEE;

        $order = DB::transaction(function () use ($customer, $address, $quantities, $products, $subtotal, $deliveryFee) {
            $order = Sales::create([
                'order_by' => $customer->name,
                'nepali_date' => NepaliDate::create(now())->toBS(),
                'customer_id' => $customer->id,
                'discount' => 0,
                'status' => 'placed',
                'channel' => 'storefront',
                'delivery_recipient' => $address->recipient_name,
                'delivery_phone' => $address->ph_number,
                'delivery_address' => $address->single_line,
                'delivery_landmark' => $address->landmark,
                'delivery_fee' => $deliveryFee,
            ]);

            foreach ($quantities as $productId => $qty) {
                SalesProduct::create([
                    'sales_id' => $order->id,
                    'product_id' => $productId,
                    'qty' => $qty,
                    'price_per_unit' => $products[$productId]->price_per_unit,
                    'payment_mode' => 'Cash on Delivery',
                    'discount' => 0,
                ]);

                // The same row the counter writes, so stock stays one number.
                InventoryStock::create([
                    'inventory_item_id' => $productId,
                    'sales_id' => $order->id,
                    'qty' => $qty,
                    'remarks' => 'Storefront order',
                ]);
            }

            $paymentModeId = DB::table('payment_modes')->where('payment_title', 'Cash on Delivery')->value('id');
            if ($paymentModeId) {
                DB::table('sales_payment_mode')->insert([
                    'sales_id' => $order->id,
                    'payment_mode_id' => $paymentModeId,
                    'amount' => $subtotal + $deliveryFee,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // The cart has become the order.
            CartItem::where('customer_id', $customer->id)->delete();

            return $order;
        });

        return response()->json([
            'order' => new OrderResource($order->load('products.inventoryItem:id,title,image')),
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
            // Deleting the sale's stock rows is what puts the units back: net
            // stock is purchases minus sales, counted from these rows.
            InventoryStock::where('sales_id', $order->id)->delete();

            $order->update(['status' => 'cancelled']);
        });

        return response()->json([
            'order' => new OrderResource($order->fresh()->load('products.inventoryItem:id,title,image')),
        ]);
    }
}
