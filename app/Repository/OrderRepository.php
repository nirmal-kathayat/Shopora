<?php

namespace App\Repository;

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\InventoryStock;
use App\Models\Sales;
use App\Models\SalesProduct;
use NepaliDate\Facades\NepaliDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Storefront orders, for the shop side. A counter sale is finished the moment
 * it is rung up, so only orders placed on the storefront show up here.
 */
class OrderRepository
{
    /** Free delivery from this subtotal up; below it, the flat fee applies. */
    public const FREE_DELIVERY_FROM = 2000;

    public const DELIVERY_FEE = 100;

    /** How a chosen method maps to the payment-mode title on the sale. */
    private const MODE_TITLES = [
        'cod' => 'Cash on Delivery',
        'esewa' => 'eSewa',
    ];

    public function __construct(private readonly CatalogueRepository $catalogue)
    {
    }

    /**
     * Turn the customer's cart into an order. Prices and stock come from the
     * database, never the browser; the only thing the customer chose is where
     * it goes and how they intend to pay.
     *
     * The order is created with the status the caller asks for - 'placed' for
     * cash on delivery, 'pending_payment' for an online payment that has not
     * cleared yet - and its stock is reserved either way. The cart is left
     * alone: the caller clears it once the order is truly committed.
     *
     * @return array{order: Sales, subtotal: float, deliveryFee: float, total: float}
     */
    public function place(Customer $customer, CustomerAddress $address, string $paymentMethod, string $status): array
    {
        $quantities = CartItem::where('customer_id', $customer->id)->pluck('qty', 'inventory_item_id');
        if ($quantities->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        $products = $this->catalogue->findMany($quantities->keys()->all())->keyBy('id');

        $subtotal = 0.0;
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
        $modeTitle = self::MODE_TITLES[$paymentMethod] ?? self::MODE_TITLES['cod'];

        $order = DB::transaction(function () use ($customer, $address, $quantities, $products, $subtotal, $deliveryFee, $paymentMethod, $status, $modeTitle) {
            $order = Sales::create([
                'order_by' => $customer->name,
                'nepali_date' => NepaliDate::create(now())->toBS(),
                'customer_id' => $customer->id,
                'discount' => 0,
                'status' => $status,
                'channel' => 'storefront',
                'delivery_recipient' => $address->recipient_name,
                'delivery_phone' => $address->ph_number,
                'delivery_address' => $address->single_line,
                'delivery_landmark' => $address->landmark,
                'delivery_fee' => $deliveryFee,
                'payment_method' => $paymentMethod,
                'payment_status' => 'unpaid',
            ]);

            foreach ($quantities as $productId => $qty) {
                SalesProduct::create([
                    'sales_id' => $order->id,
                    'product_id' => $productId,
                    'qty' => $qty,
                    'price_per_unit' => $products[$productId]->price_per_unit,
                    'payment_mode' => $modeTitle,
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

            $paymentModeId = DB::table('payment_modes')->where('payment_title', $modeTitle)->value('id');
            if ($paymentModeId) {
                DB::table('sales_payment_mode')->insert([
                    'sales_id' => $order->id,
                    'payment_mode_id' => $paymentModeId,
                    'amount' => $subtotal + $deliveryFee,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $order;
        });

        return [
            'order' => $order,
            'subtotal' => $subtotal,
            'deliveryFee' => (float) $deliveryFee,
            'total' => $subtotal + $deliveryFee,
        ];
    }

    /**
     * Give back the units an order was holding. Net stock is purchases minus
     * sales counted from these rows, so dropping them is what restores it.
     */
    public function releaseStock(Sales $order): void
    {
        InventoryStock::where('sales_id', $order->id)->delete();
    }

    /**
     * Clear out this customer's earlier, still-unpaid online orders before a
     * fresh attempt, so an abandoned checkout does not sit on its stock. Each
     * one is cancelled and its units returned.
     */
    public function cancelPendingPayments(Customer $customer): void
    {
        $stale = Sales::storefront()
            ->where('customer_id', $customer->id)
            ->where('status', 'pending_payment')
            ->get();

        foreach ($stale as $order) {
            DB::transaction(function () use ($order) {
                $this->releaseStock($order);
                $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
            });
        }
    }

    /** The list query - one row per order, totals folded in. */
    public function getOrders(?string $status = null)
    {
        $lineTotal = DB::table('sales_products')
            ->selectRaw('COALESCE(SUM(qty * price_per_unit), 0)')
            ->whereColumn('sales_products.sales_id', 'sales.id');

        $itemCount = DB::table('sales_products')
            ->selectRaw('COALESCE(SUM(qty), 0)')
            ->whereColumn('sales_products.sales_id', 'sales.id');

        $query = Sales::query()
            ->storefront()
            // An order still awaiting payment is not something the shop acts on.
            ->where('sales.status', '!=', 'pending_payment')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->select([
                'sales.id',
                'sales.status',
                'sales.payment_method',
                'sales.payment_status',
                'sales.delivery_fee',
                'sales.delivery_address',
                'sales.delivery_phone',
                'sales.created_at',
                'customers.name as customer_name',
                'customers.ph_number as customer_phone',
            ])
            ->selectSub($lineTotal, 'items_total')
            ->selectSub($itemCount, 'item_count')
            ->orderByDesc('sales.id');

        if ($status) {
            $query->where('sales.status', $status);
        }

        return $query;
    }

    public function find(int $id): Sales
    {
        return Sales::storefront()
            ->with(['products.inventoryItem:id,title,image', 'customer:id,name,email,ph_number'])
            ->findOrFail($id);
    }

    /**
     * Move an order along. Cancelling gives its units back by dropping the
     * stock rows the order wrote - net stock is purchases minus sales.
     */
    public function updateStatus(int $id, string $status): Sales
    {
        return DB::transaction(function () use ($id, $status) {
            $order = $this->find($id);

            if ($status === 'cancelled') {
                $this->releaseStock($order);
            }

            $order->update(['status' => $status]);

            return $order->refresh();
        });
    }
}
