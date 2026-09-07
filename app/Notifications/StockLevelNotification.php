<?php

namespace App\Notifications;

use App\Models\InventoryItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * What the shop is told when an item runs down.
 *
 * The dashboard has counted low stock all along, but only for somebody who
 * goes and looks at the dashboard. This is the same fact arriving instead of
 * waiting - which is the difference between reordering on Tuesday and finding
 * out on Friday that you could not sell it.
 *
 * The waiting count rides along on purpose: "out of stock" is a shrug, "out of
 * stock, 5 people waiting" is an order to place.
 */
class StockLevelNotification extends Notification
{
    use Queueable;

    /** Nothing left to sell. */
    public const OUT = 'stock_out';

    /** At or below the reorder level, but not empty yet. */
    public const LOW = 'stock_low';

    public function __construct(
        private InventoryItem $item,
        /** One of the consts above. */
        private string $level,
        /** Units left right now. */
        private int $stock,
        /** How many customers have asked to be told when it is back. */
        private int $waiting,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->level,
            'title' => $this->level === self::OUT ? 'Out of stock' : 'Running low',
            'body' => $this->item->title . ' · ' . $this->units(),
            // Who is waiting, when anyone is. This is the line that turns
            // "out of stock" from a shrug into an order to place.
            'meta' => $this->waiting > 0
                ? $this->waiting . ' waiting'
                : null,
            'item_id' => $this->item->id,
            'item_title' => $this->item->title,
            'stock' => $this->stock,
            'waiting' => $this->waiting,
            // The item's own edit screen, where stock is actually dealt with.
            'url' => route('admin.inventoryItem.edit', ['id' => $this->item->id]),
        ];
    }

    /** "none left" / "3 units left". */
    private function units(): string
    {
        return $this->stock <= 0
            ? 'none left'
            : $this->stock . ' ' . ($this->stock === 1 ? 'unit' : 'units') . ' left';
    }
}
