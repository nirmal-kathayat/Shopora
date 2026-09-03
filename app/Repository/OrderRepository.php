<?php

namespace App\Repository;

use App\Models\InventoryStock;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;

/**
 * Storefront orders, for the shop side. A counter sale is finished the moment
 * it is rung up, so only orders placed on the storefront show up here.
 */
class OrderRepository
{
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
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->select([
                'sales.id',
                'sales.status',
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
                InventoryStock::where('sales_id', $order->id)->delete();
            }

            $order->update(['status' => $status]);

            return $order->refresh();
        });
    }
}
