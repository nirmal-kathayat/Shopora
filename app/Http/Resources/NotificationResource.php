<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One notification, flattened. Laravel keeps the message in a JSON `data`
 * column; neither the storefront nor the admin bell should have to know that.
 *
 * Both sides read through here. The keys a customer's message never carries -
 * amount, customer - come back null for it, which is cheaper than a second
 * near-identical resource that would drift from this one.
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];

        return [
            'id' => $this->id,
            'event' => $data['event'] ?? null,
            'title' => $data['title'] ?? 'Update',
            'body' => $data['body'] ?? null,
            'url' => $data['url'] ?? null,
            'order_code' => $data['order_code'] ?? null,
            'status' => $data['status'] ?? null,
            'amount' => $data['amount'] ?? null,
            'customer' => $data['customer'] ?? null,
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
