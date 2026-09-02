<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroSectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'badge_text' => $this->badge_text,

            // Words wrapped in *asterisks* are meant to render in the brand
            // colour, and a newline is a line break on wide screens.
            'heading' => $this->heading,
            'subheading' => $this->subheading,

            'primary_action' => $this->action($this->primary_label, $this->primary_url),
            'secondary_action' => $this->action($this->secondary_label, $this->secondary_url),

            'image' => inventoryItemImageUrl($this->image),
            'image_alt' => $this->image_alt,

            'popular_searches' => collect($this->popular_searches ?? [])
                ->map(fn ($chip) => [
                    'label' => $chip['label'] ?? '',
                    'url' => $chip['url'] ?? null,
                ])
                ->filter(fn ($chip) => $chip['label'] !== '')
                ->values(),

            'delivery_card' => [
                'title' => $this->delivery_title,
                'subtitle' => $this->delivery_subtitle,
            ],
            'trust_card' => [
                'label' => $this->trust_label,
                'value' => $this->trust_value,
                'subtitle' => $this->trust_subtitle,
            ],

            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /** A button is only worth sending if it has something to say. */
    private function action(?string $label, ?string $url): ?array
    {
        return $label ? ['label' => $label, 'url' => $url] : null;
    }
}
