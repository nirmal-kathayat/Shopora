<?php

namespace App\Repository;

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\InventoryStock;
use App\Models\Sales;
use App\Models\SalesProduct;
use App\Notifications\OrderNotification;
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
     * Take the units again for an order whose stock was released - a payment
     * that landed after the order had been given up on. Rebuilt from the order's
     * own lines, so it takes back exactly what it gave. A no-op when the rows
     * are still there.
     */
    public function reserveStock(Sales $order): void
    {
        if (InventoryStock::where('sales_id', $order->id)->exists()) {
            return;
        }

        foreach (SalesProduct::where('sales_id', $order->id)->get() as $line) {
            InventoryStock::create([
                'inventory_item_id' => $line->product_id,
                'sales_id' => $order->id,
                'qty' => $line->qty,
                'remarks' => 'Storefront order',
            ]);
        }
    }

    /** An order's own total: its lines plus the delivery fee it was charged. */
    public function total(Sales $order): float
    {
        $lines = DB::table('sales_products')
            ->where('sales_id', $order->id)
            ->selectRaw('COALESCE(SUM(qty * price_per_unit), 0) as total')
            ->value('total');

        return (float) $lines + (float) $order->delivery_fee;
    }

    /**
     * Turn a paid-for pending order into a real one.
     *
     * The row is locked first, so two callbacks arriving at once cannot both
     * settle it: whichever gets the lock does the work, the other finds it
     * already paid and does nothing. Safe to call as many times as it is
     * reached - that is what makes the callback replayable.
     *
     * @return bool true only for the call that actually settled it
     */
    public function settlePaid(Sales $order, ?string $reference): bool
    {
        return DB::transaction(function () use ($order, $reference) {
            $fresh = Sales::whereKey($order->id)->lockForUpdate()->first();

            if (! $fresh || $fresh->payment_status === 'paid') {
                return false;
            }

            // It may have been cancelled while the money was in flight - given
            // up on as abandoned, say. It was paid for, so it stands, and the
            // units it handed back have to be taken again.
            $this->reserveStock($fresh);

            $fresh->update([
                'status' => 'placed',
                'payment_status' => 'paid',
                'payment_ref' => $reference,
            ]);

            // Only the lines this order actually took, and only if they have
            // not been touched since it was made. A payment can settle long
            // after the fact - a reconciliation run picking up a callback that
            // never arrived - and whatever the customer has put in the cart
            // since is a fresh shop that must survive it. The test is
            // updated_at, not created_at: a cart holds one line per product, so
            // re-adding something bumps the line it already has.
            CartItem::where('customer_id', $fresh->customer_id)
                ->whereIn('inventory_item_id', SalesProduct::where('sales_id', $fresh->id)->pluck('product_id'))
                ->where('updated_at', '<=', $fresh->created_at)
                ->delete();

            $this->tell($fresh, OrderNotification::PAYMENT_RECEIVED);

            return true;
        });
    }

    /**
     * Give up on an order that was never paid for: cancel it and return its
     * units. Locked and status-checked, so a payment that settled in the
     * meantime can never be undone by a late failure.
     *
     * @return bool true only for the call that actually cancelled it
     */
    public function failPending(Sales $order): bool
    {
        return DB::transaction(function () use ($order) {
            $fresh = Sales::whereKey($order->id)->lockForUpdate()->first();

            if (! $fresh || $fresh->status !== 'pending_payment') {
                return false;
            }

            $this->releaseStock($fresh);
            $fresh->update(['status' => 'cancelled', 'payment_status' => 'failed']);

            $this->tell($fresh, OrderNotification::PAYMENT_FAILED);

            return true;
        });
    }

    /**
     * Orders sitting mid-payment. With $olderThanMinutes, only those that have
     * sat there long enough that a customer still at the gateway is unlikely.
     */
    public function pendingPayments(?int $olderThanMinutes = null)
    {
        $query = Sales::storefront()
            ->where('status', 'pending_payment')
            ->whereNotNull('payment_uuid');

        if ($olderThanMinutes !== null) {
            $query->where('created_at', '<=', now()->subMinutes($olderThanMinutes));
        }

        return $query;
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
            $this->failPending($order);
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
            $this->tell($order, $status);

            return $order->refresh();
        });
    }

    /**
     * Tell the customer what just happened to their order.
     *
     * This sits in the repository rather than the controllers on purpose: a
     * payment settles from two places - the browser coming back from eSewa and
     * the reconcile command picking up the callback that never arrived - and a
     * notification written in a controller would only fire for whichever path
     * happened to run. Sending after commit means a rolled-back transaction
     * never leaves a message about something that did not happen.
     *
     * Only storefront orders: a counter sale's customer never placed it online
     * and has no account to read it in.
     */
    private function tell(Sales $order, string $event): void
    {
        if (($order->channel ?? 'counter') !== 'storefront' || ! $order->customer_id) {
            return;
        }

        DB::afterCommit(function () use ($order, $event) {
            $customer = Customer::find($order->customer_id);
            $customer?->notify(new OrderNotification($order, $event));
        });
    }
}
