<?php

namespace App\Notifications;

use App\Models\ProductReview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * What the shop is told when a customer reviews a product.
 *
 * Reviews go live the moment they are written - there is no approval step -
 * so a one-star complaint is on the product page whether or not anyone at the
 * shop has seen it. This is what makes sure somebody has.
 *
 * A poor rating takes its own tone in the bell, because it is the one that
 * needs answering today rather than reading later.
 */
class ReviewNotification extends Notification
{
    use Queueable;

    public const GOOD = 'review_posted';

    /** Two stars or fewer - a complaint, and treated as one. */
    public const POOR = 'review_poor';

    public function __construct(private ProductReview $review)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $rating = (int) $this->review->rating;
        $poor = $rating <= 2;

        return [
            'event' => $poor ? self::POOR : self::GOOD,
            'title' => $poor ? 'Poor review' : 'New review',
            // What they wrote leads, because that is what needs answering.
            'body' => $this->said(),
            'meta' => str_repeat('★', $rating) . str_repeat('☆', 5 - $rating)
                . ' · ' . ($this->review->inventoryItem?->title ?? 'A product'),
            'review_id' => $this->review->id,
            'item_id' => $this->review->inventory_item_id,
            'rating' => $rating,
            'customer' => $this->review->customer?->name,
            'url' => route('admin.review'),
        ];
    }

    /** What they actually wrote, trimmed to something a bell row can hold. */
    private function said(): string
    {
        $said = trim((string) ($this->review->title ?: $this->review->body));

        if ($said === '') {
            return 'They left a rating without a comment.';
        }

        return mb_strlen($said) > 120 ? mb_substr($said, 0, 119) . '…' : $said;
    }
}
