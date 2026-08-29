<?php

namespace App\Repository;

use App\Models\InventoryItem;

class InventoryItemRepository
{
    private $query;

    public function __construct(InventoryItem $query)
    {
        $this->query = $query;
    }
    public function getInventoryTitle()
    {
        return $this->query->select('id', 'title')->get();
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
                'inventory_items.category_id',
                'categories.title as category_title'
            );

        // Filter by category if provided
        if ($categoryId && $categoryId !== '') {
            $query->where('inventory_items.category_id', $categoryId);
        }

        return $query->orderBy('inventory_items.id', 'desc');
    }
    public function storeInventoryItem(array $data)
    {
        // $code = $this->countInventories();
        return $this->query->create([
            'title' => $data['title'],
            'unit' => $data['unit'],
            'code' => $data['code'],
            'category_id' => $data['category_id'],
            'price_per_unit' => $data['price_per_unit']
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
            'price_per_unit' => $data['price_per_unit']
        ];
        return $this->query->where('id', $id)->update($inventory);
    }

    public function delete($id)
    {
        return $this->query->where('id', $id)->delete($id);
    }

    public function countInventories()
    {
        $data =  $this->query->orderBy('id', 'desc')->value('id');
        $data = "SSH-" . $data + 1;
        return $data;
    }
}
