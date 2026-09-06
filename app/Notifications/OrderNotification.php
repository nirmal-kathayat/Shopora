<?php

namespace App\Notifications;

use App\Models\Sales;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * What a customer is told about one of their orders.
 *
 * One class rather than one per event: every message is the same shape - a
 * line about an order, a link back to it - and keeping the wording for the
 * whole journey in a single table is what stops "shipped" and "delivered"
 * drifting into two different voices.
 */
class OrderNotification extends Notification
{
    use Queueable;

    /** Order status -> the message the customer gets. */
    private const STATUS_COPY = [
        'confirmed' => [
            'title' => 'Order confirmed',
            'body' => 'We have your order and are getting it ready.',
        ],
        'shipped' => [
            'title' => 'Order on the way',
            'body' => 'Your order has left the shop and is out for delivery.',
        ],
        'delivered' => [
            'title' => 'Order delivered',
            'body' => 'Your order has been delivered. Thank you for shopping with us.',
        ],
        'cancelled' => [
            'title' => 'Order cancelled',
            'body' => 'This order has been cancelled. Anything already paid will be refunded.',
        ],
        'placed' => [
            'title' => 'Order placed',
            'body' => 'We have received your order.',
        ],
    ];

    /** Events that are not a status change of their own. */
    public const PAYMENT_RECEIVED = 'payment_received';
    public const PAYMENT_FAILED = 'payment_failed';

    private const EVENT_COPY = [
        self::PAYMENT_RECEIVED => [
            'title' => 'Payment received',
            'body' => 'Your payment has gone through and your order is confirmed.',
        ],
        self::PAYMENT_FAILED => [
            'title' => 'Payment not completed',
            'body' => 'The payment did not go through, so this order was released. You can order again.',
        ],
    ];

    public function __construct(
        private Sales $order,
        /** A status from Sales::FLOW, or one of the PAYMENT_* events. */
        private string $event,
    ) {
    }

    /**
     * Mail is off unless the shop turns it on. The default .env still points at
     * a local mailpit, and with QUEUE_CONNECTION=sync a refused SMTP connection
     * would throw inside the request that marked the order shipped - losing the
     * shipment over an undelivered email. Turn it on together with a queue.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('shopora.notify_by_email') && $this->wantsEmail() && $notifiable->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        $copy = $this->copy();

        return [
            'event' => $this->event,
            'title' => $copy['title'] . ' · ' . $this->order->code,
            'body' => $copy['body'],
            'order_id' => $this->order->id,
            'order_code' => $this->order->code,
            'status' => $this->order->status,
            // Straight to the order it is about, not just the list of them -
            // the storefront opens that one row when it lands.
            'url' => '/account?section=orders&order=' . $this->order->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $copy = $this->copy();

        return (new MailMessage())
            ->subject($copy['title'] . ' · ' . $this->order->code)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($copy['body'])
            ->action('View your order', url('/account?section=orders'))
            ->line('Order ' . $this->order->code . '.');
    }

    /** Only the ends of the journey are worth an email; the rest are in-app. */
    private function wantsEmail(): bool
    {
        return in_array($this->event, ['delivered', 'cancelled', self::PAYMENT_RECEIVED], true);
    }

    private function copy(): array
    {
        return self::EVENT_COPY[$this->event]
            ?? self::STATUS_COPY[$this->event]
            ?? ['title' => 'Order update', 'body' => 'There is an update on your order.'];
    }
}
