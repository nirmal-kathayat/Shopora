<?php

namespace App\Http\Resources;

use App\Repository\CatalogueRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $price = (float) $this->price_per_unit;
        $compare = $this->compare_at_price !== null ? (float) $this->compare_at_price : null;
        $stock = (int) $this->stock_qty;

        return [
            'id' => $this->id,
            'name' => $this->title,
            'code' => $this->code,
            'unit' => $this->unit,
            'price' => $price,
            // Only a genuine "was" price - above the current one - is worth
            // sending; anything else would render a fake discount.
            'compare_at_price' => $compare !== null && $compare > $price ? $compare : null,
            'image' => inventoryItemImageUrl($this->image),
            // The full gallery, main image first, on the single-product view.
            'images' => $this->when(
                $this->relationLoaded('productImages'),
                fn () => collect([$this->image])
                    ->merge($this->productImages->pluck('image'))
                    ->filter()
                    ->map(fn ($file) => inventoryItemImageUrl($file))
                    ->values()
            ),
            'category' => [
                'title' => $this->category_title,
                'slug' => $this->category_slug,
            ],
            'stock_qty' => $stock,
            'stock_level' => $this->stockLevel($stock),
            // The detail columns only when the query loaded them (single view),
            // so the list stays lean.
            $this->mergeWhen($this->resource->description !== null || $this->extraColumnsLoaded(), [
                'description' => $this->description,
                'brand' => $this->brand,
                'net_volume' => $this->net_volume,
                'country_of_origin' => $this->country_of_origin,
                'highlights' => $this->normalizeHighlights(),
                'features' => array_values(array_filter((array) ($this->features ?? []))),
            ]),
            // The real average and count; null when the product has no reviews,
            // so the storefront shows "no reviews yet" rather than a zero.
            'rating' => $this->review_count > 0 ? round((float) $this->review_avg, 1) : null,
            'reviews' => $this->review_count > 0 ? (int) $this->review_count : null,
        ];
    }

    /** True when the single-product query selected the detail columns. */
    private function extraColumnsLoaded(): bool
    {
        return array_key_exists('brand', $this->resource->getAttributes())
            || array_key_exists('description', $this->resource->getAttributes());
    }

    /** Highlights come back from the JSON cast as an array of maps. */
    private function normalizeHighlights(): array
    {
        return collect($this->highlights ?? [])
            ->map(fn ($h) => [
                'icon' => $h['icon'] ?? 'sparkles',
                'title' => $h['title'] ?? '',
                'subtitle' => $h['subtitle'] ?? null,
            ])
            ->filter(fn ($h) => $h['title'] !== '')
            ->values()
            ->all();
    }

    private function stockLevel(int $qty): string
    {
        if ($qty <= 0) {
            return 'out';
        }

        return $qty <= CatalogueRepository::LOW_STOCK_THRESHOLD ? 'low' : 'in';
    }
}
