<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealSectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // Words wrapped in *asterisks* render in the brand green.
            'heading' => $this->heading,
            'subheading' => $this->subheading,
            'image' => inventoryItemImageUrl($this->image),
            'image_alt' => $this->image_alt,
            'cards' => DealCardResource::collection($this->cards),
            'trust_items' => collect($this->trust_items ?? [])
                ->map(fn ($item) => [
                    'icon' => $item['icon'] ?? 'badge-check',
                    'title' => $item['title'] ?? '',
                    'subtitle' => $item['subtitle'] ?? null,
                ])
                ->filter(fn ($item) => $item['title'] !== '')
                ->values(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
