<?php

namespace App\Repository;

use App\Models\InventoryItem;
use Illuminate\Http\UploadedFile;

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
                'inventory_items.category_id',
                'inventory_items.image',
                'categories.title as category_title'
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

        return $this->query->where('id', $id)->delete();
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
