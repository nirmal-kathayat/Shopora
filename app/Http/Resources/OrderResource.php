<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A storefront order. Prices are the ones recorded on the sale, not today's
 * catalogue prices - an order says what was actually charged.
 */
class OrderResource extends JsonResource
{
    private const LABELS = [
        'cod' => 'Cash on Delivery',
        'esewa' => 'eSewa',
    ];

    private function payment_label(): string
    {
        return self::LABELS[$this->payment_method] ?? 'Cash on Delivery';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = $this->products->map(fn ($line) => [
            'product_id' => (int) $line->product_id,
            'name' => $line->inventoryItem?->title ?? 'Product',
            'image' => inventoryItemImageUrl($line->inventoryItem?->image),
            'qty' => (int) $line->qty,
            'price_per_unit' => (float) $line->price_per_unit,
            'line_total' => (float) $line->price_per_unit * (int) $line->qty,
        ]);

        $subtotal = $items->sum('line_total');

        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'placed_at' => $this->created_at?->toIso8601String(),
            // 'cod' / 'esewa' as stored, plus a label the storefront can show
            // as-is, and whether the money is actually in.
            'payment_method' => $this->payment_label(),
            'payment_status' => $this->payment_status ?? 'unpaid',
            'is_paid' => ($this->payment_status ?? 'unpaid') === 'paid',

            'items' => $items->values(),
            'item_count' => (int) $items->sum('qty'),

            'subtotal' => $subtotal,
            'delivery_fee' => (float) $this->delivery_fee,
            'total' => $subtotal + (float) $this->delivery_fee,

            'delivery' => [
                'recipient' => $this->delivery_recipient,
                'phone' => $this->delivery_phone,
                'address' => $this->delivery_address,
                'landmark' => $this->delivery_landmark,
            ],

            // What the customer may still do with it.
            'can_cancel' => in_array($this->status, ['placed', 'confirmed'], true),
        ];
    }
}
