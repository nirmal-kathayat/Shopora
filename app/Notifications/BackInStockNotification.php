<?php

namespace App\Notifications;

use App\Models\InventoryItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "The thing you asked about is back."
 *
 * The one message a customer actually asked to receive, so it takes email when
 * the shop has mail configured - being told a week later, in the app, that
 * something was briefly back is worse than not being told.
 */
class BackInStockNotification extends Notification
{
    use Queueable;

    public const EVENT = 'back_in_stock';

    public function __construct(private InventoryItem $item)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('shopora.notify_by_email') && $notifiable->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event' => self::EVENT,
            'title' => 'Back in stock',
            'body' => $this->item->title . ' is available again.',
            'item_id' => $this->item->id,
            'item_title' => $this->item->title,
            'url' => '/product/' . $this->item->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->item->title . ' is back in stock')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($this->item->title . ' is available again at Shopora.')
            ->action('View it', url('/product/' . $this->item->id))
            ->line('We will not message you about this item again unless you ask.');
    }
}
