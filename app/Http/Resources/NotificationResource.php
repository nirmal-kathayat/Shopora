<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One notification, flattened for the storefront. Laravel keeps the message in
 * a JSON `data` column; the client should not have to know that.
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
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
