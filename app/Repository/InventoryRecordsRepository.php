<?php

namespace App\Repository;

use App\Models\InventoryRecord;
use App\Models\Product;

class InventoryRecordsRepository
{
    private $records, $product;

    public function __construct(InventoryRecord $records, Product $product)
    {
        $this->records = $records;
        $this->product = $product;
    }

    public function storeInventoryRecords(array $data)
    {
        $inventoryRecord = $this->records->create([
            'type' => $data['type'] ?? 'inventory',
            'date' => $data['date'],
            'qty' => $data['qty'],
            'order_id' => $data['order_id'] ?? null,
            'product_id' => $data['product_id']
        ]);
        $product = $this->product->findOrFail($data['product_id']);
        $product->update([
            'qty' => $product->qty + $data['qty']
        ]);
        return $inventoryRecord;
    }
    public function getInventoryRecords($productId)
    {
        return $this->records->where('product_id', $productId)
            ->orderBy('date', 'desc')
            ->get();
    }
}
