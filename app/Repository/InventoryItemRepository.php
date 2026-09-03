<?php

namespace App\Repository;

use App\Models\InventoryItem;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class InventoryItemRepository
{
    private $query;

    public function __construct(InventoryItem $query)
    {
        $this->query = $query;
    }

    public function getInventoryTitle()
    {
        return $this->query->select('id', 'title', 'image')->get();
    }

    public function getInventoryItems($categoryId = null)
    {
        $query = $this->query
            ->leftJoin('categories', 'categories.id', '=', 'inventory_items.category_id')
            ->select(
                'inventory_items.id',
                'inventory_items.title',
                'inventory_items.code',
                'inventory_items.unit',
                'inventory_items.price_per_unit',
                'inventory_items.compare_at_price',
                'inventory_items.description',
                'inventory_items.brand',
                'inventory_items.net_volume',
                'inventory_items.country_of_origin',
                'inventory_items.category_id',
                'inventory_items.image',
                'categories.title as category_title'
            )
            // How many customers have this saved. A subquery keeps the row
            // count honest - a join here would multiply the product rows.
            ->selectSub(
                DB::table('wishlist_items')
                    ->selectRaw('count(*)')
                    ->whereColumn('wishlist_items.inventory_item_id', 'inventory_items.id'),
                'wishlist_count'
            );

        if ($categoryId && $categoryId !== '') {
            $query->where('inventory_items.category_id', $categoryId);
        }

        return $query->orderBy('inventory_items.id', 'desc');
    }

    public function storeInventoryItem(array $data)
    {
        return $this->query->create([
            'title' => $data['title'],
            'unit' => $data['unit'],
            'code' => $data['code'],
            'category_id' => $data['category_id'],
            'price_per_unit' => $data['price_per_unit'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'description' => $data['description'] ?? null,
            'brand' => $data['brand'] ?? null,
            'net_volume' => $data['net_volume'] ?? null,
            'country_of_origin' => $data['country_of_origin'] ?? null,
            'highlights' => $data['highlights'] ?? [],
            'features' => $data['features'] ?? [],
            'image' => $data['image'] ?? null,
        ]);
    }

    public function find($id)
    {
        return $this->query->findOrFail($id);
    }

    public function updateInventoryItem(array $data, int $id)
    {
        $inventory = [
            'title' => $data['title'],
            'unit' => $data['unit'],
            'code' => $data['code'],
            'category_id' => $data['category_id'],
            'price_per_unit' => $data['price_per_unit'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'description' => $data['description'] ?? null,
            'brand' => $data['brand'] ?? null,
            'net_volume' => $data['net_volume'] ?? null,
            'country_of_origin' => $data['country_of_origin'] ?? null,
            'highlights' => $data['highlights'] ?? [],
            'features' => $data['features'] ?? [],
        ];

        if (array_key_exists('image', $data)) {
            $inventory['image'] = $data['image'];
        }

        return $this->query->where('id', $id)->update($inventory);
    }

    public function delete($id)
    {
        $item = $this->query->findOrFail($id);
        $this->deleteImageFile($item->image);

        // The rows cascade with the item; the files on disk do not.
        foreach ($item->productImages as $image) {
            $this->deleteImageFile($image->image);
        }

        return $this->query->where('id', $id)->delete();
    }

    /**
     * Append uploaded gallery photos to an item, continuing the sort order.
     *
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     */
    public function addGalleryImages(int $itemId, array $files): void
    {
        $next = (int) ProductImage::where('inventory_item_id', $itemId)->max('sort_order');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            ProductImage::create([
                'inventory_item_id' => $itemId,
                'image' => $this->storeImageFile($file),
                'sort_order' => ++$next,
            ]);
        }
    }

    /**
     * Remove specific gallery images (by id) belonging to this item, files and
     * rows both.
     *
     * @param  array<int, int|string>  $imageIds
     */
    public function removeGalleryImages(int $itemId, array $imageIds): void
    {
        $ids = array_filter(array_map('intval', $imageIds));
        if (empty($ids)) {
            return;
        }

        ProductImage::where('inventory_item_id', $itemId)
            ->whereIn('id', $ids)
            ->get()
            ->each(function (ProductImage $image) {
                $this->deleteImageFile($image->image);
                $image->delete();
            });
    }

    public function storeImageFile(UploadedFile $file): string
    {
        $directory = public_path('image');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = 'inv_' . time() . '_' . uniqid() . '.' . $extension;
        $file->move($directory, $filename);

        return $filename;
    }

    public function deleteImageFile(?string $filename): void
    {
        if (empty($filename)) {
            return;
        }

        $path = public_path('image/' . ltrim($filename, '/'));
        if (is_file($path)) {
            unlink($path);
        }
    }

    public function countInventories()
    {
        $data = $this->query->orderBy('id', 'desc')->value('id');
        $data = 'SSH-' . $data + 1;

        return $data;
    }
}
