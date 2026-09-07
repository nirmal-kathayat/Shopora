<?php

namespace App\Repository;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryStock;
use App\Models\StockAlert;
use App\Notifications\BackInStockNotification;
use App\Notifications\StockLevelNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Everything that happens because an item's stock moved.
 *
 * Two audiences, one trigger, so one class: the shop wants to hear that
 * something is running out, and the customers who pressed "Notify me" want to
 * hear that it is back. Both hang off the same question - what is the net
 * stock now, and which side of a line did it just cross.
 *
 * Net stock is not stored anywhere; it is purchases minus sales, summed on
 * read. So "did it cross" cannot be answered by comparing to a previous value
 * - inventory_items.stock_alert_level is what remembers where the shop was
 * last told this item stood.
 */
class StockAlertRepository
{
    /** At or below this many units, the shop is asked to reorder. */
    public static function reorderLevel(): int
    {
        return max(0, (int) config('shopora.reorder_level', 10));
    }

    // ---------------------------------------------------------------- watching

    /**
     * Look at these items again and send whatever their new level calls for.
     *
     * Safe to call as often as it is reached: the latch means a second call at
     * the same level says nothing, so a purchase writing twenty rows announces
     * an item once, not twenty times.
     *
     * @param  iterable<int>  $itemIds
     */
    public function check(iterable $itemIds): void
    {
        $levels = InventoryStock::netFor($itemIds);
        if (! $levels) {
            return;
        }

        $items = InventoryItem::whereIn('id', array_keys($levels))->get()->keyBy('id');

        foreach ($levels as $itemId => $stock) {
            $item = $items->get($itemId);
            if ($item) {
                $this->settle($item, $stock);
            }
        }
    }

    /** The same, for one item. */
    public function checkOne(int $itemId): void
    {
        $this->check([$itemId]);
    }

    /**
     * The same again, but held until the surrounding transaction commits.
     *
     * Every write path uses this rather than check(): a sale that rolls back
     * must not have told five customers their item was back, and outside a
     * transaction Laravel runs the callback immediately, so there is no case
     * where it is the wrong choice.
     *
     * @param  iterable<int>  $itemIds
     */
    public function checkLater(iterable $itemIds): void
    {
        $ids = array_values(array_unique(array_map('intval', iterator_to_array(
            is_array($itemIds) ? new \ArrayIterator($itemIds) : $itemIds
        ))));

        if (! $ids) {
            return;
        }

        DB::afterCommit(function () use ($ids) {
            $this->check($ids);
        });
    }

    /**
     * One item: release anyone waiting for it, then tell the shop if it has
     * crossed into a level it was not already at.
     */
    private function settle(InventoryItem $item, int $stock): void
    {
        if ($stock > 0) {
            $this->release($item);
        }

        $level = match (true) {
            $stock <= 0 => StockLevelNotification::OUT,
            $stock <= self::reorderLevel() => StockLevelNotification::LOW,
            default => null,
        };

        // Back above the line: forget what it was told, so the next fall is
        // announced again rather than swallowed by a stale latch.
        if ($level === null) {
            if ($item->stock_alert_level !== null) {
                $item->forceFill(['stock_alert_level' => null])->save();
            }

            return;
        }

        // Already said, and nothing has changed about it.
        if ($item->stock_alert_level === $level) {
            return;
        }

        $item->forceFill(['stock_alert_level' => $level])->save();

        $waiting = StockAlert::where('inventory_item_id', $item->id)->count();
        $staff = Admin::where('status', 1)->get();

        if ($staff->isNotEmpty()) {
            Notification::send($staff, new StockLevelNotification($item, $level, $stock, $waiting));
        }
    }

    /**
     * Tell everyone who asked that this item is back, then drop their rows.
     *
     * The request is standing, not permanent: once it has been answered the
     * customer is not on a list any more, which is also what keeps the waiting
     * count on the shop's side an honest number rather than a running total.
     */
    private function release(InventoryItem $item): void
    {
        $alerts = StockAlert::where('inventory_item_id', $item->id)->get();
        if ($alerts->isEmpty()) {
            return;
        }

        $customers = Customer::whereIn('id', $alerts->pluck('customer_id'))->get();

        StockAlert::whereIn('id', $alerts->pluck('id'))->delete();

        if ($customers->isNotEmpty()) {
            Notification::send($customers, new BackInStockNotification($item));
        }
    }

    // ---------------------------------------------------------------- the list

    /**
     * Put this customer on the list for an item. Asking twice is the same
     * request, so it is written as one row either way.
     */
    public function join(Customer $customer, int $itemId): void
    {
        DB::table('stock_alerts')->updateOrInsert(
            ['inventory_item_id' => $itemId, 'customer_id' => $customer->id],
            ['updated_at' => now(), 'created_at' => now()],
        );
    }

    public function leave(Customer $customer, int $itemId): void
    {
        StockAlert::where('inventory_item_id', $itemId)
            ->where('customer_id', $customer->id)
            ->delete();
    }

    /**
     * The item ids this customer is waiting on, so the storefront can draw
     * every "Notify me" button in the right state from one request.
     *
     * @return array<int, int>
     */
    public function waitingFor(Customer $customer): array
    {
        return StockAlert::where('customer_id', $customer->id)
            ->pluck('inventory_item_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
