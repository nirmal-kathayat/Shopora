<?php

namespace App\Notifications;

use App\Models\Sales;
use App\Repository\OrderRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * What the shop is told about a storefront order.
 *
 * The counterpart of OrderNotification, which tells the customer. The two are
 * deliberately separate classes: the customer is told what is happening to
 * their order ("your order is on the way"), the shop is told what it now has
 * to do about it ("a new order is waiting"), and folding both voices into one
 * table of copy is how they end up sounding like neither.
 *
 * Only events the shop can act on land here. An admin moving an order along
 * themselves does not, because they were the one who moved it.
 */
class ShopOrderNotification extends Notification
{
    use Queueable;

    /** A cash order the customer has just placed. */
    public const ORDER_PLACED = 'order_placed';

    /** An online order whose money has cleared - the point it becomes real. */
    public const PAYMENT_RECEIVED = 'payment_received';

    /** An online order given up on: never paid for, stock handed back. */
    public const PAYMENT_FAILED = 'payment_failed';

    /** The customer called it off themselves, before the shop shipped it. */
    public const CUSTOMER_CANCELLED = 'customer_cancelled';

    private const COPY = [
        self::ORDER_PLACED => [
            'title' => 'New order',
            'body' => 'A new order is waiting to be confirmed.',
        ],
        self::PAYMENT_RECEIVED => [
            'title' => 'New paid order',
            'body' => 'Payment has cleared. The order is ready to be confirmed.',
        ],
        self::PAYMENT_FAILED => [
            'title' => 'Payment not completed',
            'body' => 'The order was released and its stock returned.',
        ],
        self::CUSTOMER_CANCELLED => [
            'title' => 'Cancelled by customer',
            'body' => 'The customer called this order off. Its stock has been returned.',
        ],
    ];

    public function __construct(
        private Sales $order,
        /** One of the consts above. */
        private string $event,
        /** The order's own total, worked out once by the caller. */
        private float $total,
    ) {
    }

    /**
     * The bell only. The shop side is a screen somebody is sitting at, not an
     * inbox - and mail here would put the same news in two places with no way
     * to mark it read in one of them.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $copy = self::COPY[$this->event] ?? ['title' => 'Order update', 'body' => ''];

        return [
            'event' => $this->event,
            // The order code goes on the timestamp line, not in the title: in
            // a 380px panel "Cancelled by customer · ORD-2026-0095" truncates,
            // and the half that gets cut is the half that says which order.
            'title' => $copy['title'],
            // Who and how much, on the row itself: the whole point of the bell
            // is deciding whether to get up, and that needs the amount.
            'body' => $this->who() . ' · Rs. ' . number_format($this->total, 2)
                . ' · ' . $this->how(),
            'note' => $copy['body'],
            'order_id' => $this->order->id,
            'order_code' => $this->order->code,
            'status' => $this->order->status,
            'amount' => round($this->total, 2),
            'customer' => $this->who(),
            // Straight to the order, not the list: the Orders screen opens
            // this one row when it lands.
            'url' => route('admin.order') . '?order=' . $this->order->id,
        ];
    }

    /** The customer's name, or the delivery phone when the account has none. */
    private function who(): string
    {
        $name = trim((string) ($this->order->customer?->name ?? ''));

        return $name !== '' ? $name : (string) ($this->order->delivery_phone ?: 'Customer');
    }

    /**
     * How it was paid, short. The bell row is one line wide and the mode is
     * the last thing on it, so "Cash on Delivery" is the part that gets cut -
     * and COD is what the shop calls it anyway.
     */
    private function how(): string
    {
        $short = ['cod' => 'COD', 'esewa' => 'eSewa'];
        $method = (string) $this->order->payment_method;

        return $short[$method]
            ?? OrderRepository::MODE_TITLES[$method]
            ?? ucfirst($method);
    }
}
