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
            'category' => [
                'title' => $this->category_title,
                'slug' => $this->category_slug,
            ],
            'stock_qty' => $stock,
            'stock_level' => $this->stockLevel($stock),
            // No reviews yet - the storefront hides the rating row when these
            // are null rather than showing a made-up score.
            'rating' => null,
            'reviews' => null,
        ];
    }

    private function stockLevel(int $qty): string
    {
        if ($qty <= 0) {
            return 'out';
        }

        return $qty <= CatalogueRepository::LOW_STOCK_THRESHOLD ? 'low' : 'in';
    }
}
