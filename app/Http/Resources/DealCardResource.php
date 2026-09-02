<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'badge_text' => $this->badge_text,
            // Words wrapped in *asterisks* render in the accent colour.
            'title' => $this->title,
            'description' => $this->description,
            'cta' => $this->cta_label ? ['label' => $this->cta_label, 'url' => $this->cta_url] : null,
            // Also picks the built-in artwork when no image is set.
            'icon' => $this->icon,
            'image' => inventoryItemImageUrl($this->image),
            'image_alt' => $this->image_alt,
            'featured' => (bool) $this->featured,
        ];
    }
}
