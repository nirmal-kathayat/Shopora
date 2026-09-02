<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer?->name ?? 'Customer',
            'rating' => (int) $this->rating,
            'title' => $this->title,
            'body' => $this->body,
            'created_at' => $this->created_at?->toIso8601String(),
            // The signed-in customer's own review, so the UI can label it.
            'is_mine' => $request->user() !== null && $request->user()->id === $this->customer_id,
        ];
    }
}
