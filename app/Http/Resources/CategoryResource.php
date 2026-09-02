<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            // A keyword the storefront maps to a lucide icon.
            'icon' => $this->icon,
            'image' => inventoryItemImageUrl($this->image),
            'image_alt' => $this->image_alt,
            // Present only when the query loaded it; the storefront shows it as
            // the "N items" line under each aisle.
            'item_count' => $this->when(
                $this->inventory_items_count !== null,
                (int) $this->inventory_items_count,
            ),
        ];
    }
}
