<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryStock extends Model
{
    protected $guarded = [];
    protected $table = 'inventory_stocks';

    /**
     * Stock moved, so anything that hangs off the level is looked at again -
     * the shop's reorder bell and the customers waiting on "Notify me".
     *
     * After commit, so a rolled-back sale never tells five people that
     * something is back. Idempotent by design: the check writes nothing unless
     * the item has crossed a line it was not already on, so a purchase writing
     * twenty rows still announces the item once.
     *
     * Only single-row work passes through here. A mass delete - releasing an
     * order's units, rewriting a purchase - fires no model events at all, so
     * those sites call the repository themselves.
     */
    protected static function booted(): void
    {
        $look = function (self $row) {
            $itemId = (int) $row->inventory_item_id;
            if (! $itemId) {
                return;
            }

            app(\App\Repository\StockAlertRepository::class)->checkLater([$itemId]);
        };

        static::created($look);
        static::deleted($look);
    }

    /**
     * Units on hand, per item: everything a purchase brought in, less
     * everything a sale took out. Nothing stores this - it is the rows.
     *
     * A row with neither a purchase nor a sale behind it is an adjustment
     * that predates the current bookkeeping and is deliberately counted as
     * neither, which is the rule the storefront's own stock column follows.
     *
     * @param  iterable<int>  $itemIds
     * @return array<int, int>  item id => units, including the ones at zero
     */
    public static function netFor(iterable $itemIds): array
    {
        $ids = array_values(array_unique(array_map('intval', iterator_to_array(
            is_array($itemIds) ? new \ArrayIterator($itemIds) : $itemIds
        ))));

        if (! $ids) {
            return [];
        }

        $rows = static::query()
            ->whereIn('inventory_item_id', $ids)
            ->groupBy('inventory_item_id')
            ->pluck(
                DB::raw('COALESCE(SUM(CASE WHEN purchase_inventory_id IS NOT NULL THEN qty ELSE 0 END)'
                    . ' - SUM(CASE WHEN sales_id IS NOT NULL THEN qty ELSE 0 END), 0)'),
                'inventory_item_id'
            );

        // An item with no rows at all has no stock, and must still be answered
        // for - that is exactly the item that has just run out.
        return array_map('intval', array_replace(array_fill_keys($ids, 0), $rows->all()));
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function purchaseInventory()
    {
        return $this->belongsTo(PurchaseInventory::class);
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }
}
