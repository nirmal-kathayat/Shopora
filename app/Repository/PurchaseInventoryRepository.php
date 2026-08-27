<?php

namespace App\Repository;

use DB;
use App\Models\PurchaseInventory;
use App\Models\PurchaseInventoryItem;
use App\Models\InventoryStock;
use Carbon\Carbon;

class PurchaseInventoryRepository
{
    private $query;
    public function __construct(PurchaseInventory $query)
    {
        $this->query = $query;
    }

    public function getPurchaseInventory()
    {
        return $this->query
            ->select(
                'purchase_inventory.id',
                'purchase_inventory.vendor as vendor_name',
                'purchase_inventory.bill_date as purchase_date',
                'purchase_inventory.vat_amount',
            )->orderBy('purchase_inventory.id', 'desc');
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            // Create main purchase inventory record
            $purchaseInventory = $this->query->create([
                'vendor' => $data['vendor_name'],
                'bill_date' => $data['bill_date'],
                'address' => $data['address'] ?? null,
                'pan_number' => $data['pan_number'] ?? null,
                'vat_amount' => $data['vat_amount'] ?? null,
            ]);

            // Create purchase inventory items
            foreach ($data['inventory_items'] as $item) {
                PurchaseInventoryItem::create([
                    'purchase_inventory_id' => $purchaseInventory->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'qty' => $item['qty'],
                    'rate' => $item['rate'],
                ]);

                // Create inventory stock records
                InventoryStock::create([
                    'inventory_item_id' => $item['inventory_item_id'],
                    'purchase_inventory_id' => $purchaseInventory->id,
                    'qty' => $item['qty'],
                    'remarks' => 'Purchase from ' . $data['vendor_name'],
                ]);

                // Store record for purchase
                // DB::table('store_records')->insert([
                //     'inventory_item_id' => $item['inventory_item_id'],
                //     'qty' => $item['qty'],
                //     'purchase_inventory_id' => $purchaseInventory->id,
                //     'type' => 'purchase',
                //     'created_at' => now(),
                //     'updated_at' => now()
                // ]);
            }

            DB::commit();
            return $purchaseInventory->id;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function find($id)
    {
        return $this->query->with('items.inventoryItem')->findOrFail($id);
    }

    public function update(array $data, int $id)
    {
        try {
            DB::beginTransaction();

            $purchaseInventory = $this->query->findOrFail($id);

            // Update main purchase inventory record
            $purchaseInventory->update([
                'vendor' => $data['vendor_name'],
                'bill_date' => $data['bill_date'],
                'address' => $data['address'] ?? null,
                'pan_number' => $data['pan_number'] ?? null,
                'vat_amount' => $data['vat_amount'] ?? null,
            ]);

            // Delete existing items and stocks
            PurchaseInventoryItem::where('purchase_inventory_id', $id)->delete();
            InventoryStock::where('purchase_inventory_id', $id)->delete();

            // Create new purchase inventory items
            foreach ($data['inventory_items'] as $item) {
                PurchaseInventoryItem::create([
                    'purchase_inventory_id' => $id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'qty' => $item['qty'],
                    'rate' => $item['rate'],
                ]);

                // Create inventory stock records
                InventoryStock::create([
                    'inventory_item_id' => $item['inventory_item_id'],
                    'purchase_inventory_id' => $id,
                    'qty' => $item['qty'],
                    'remarks' => 'Purchase from ' . $data['vendor_name'],
                ]);
            }

            DB::commit();
            return $purchaseInventory;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $purchaseInventory = $this->query->findOrFail($id);

            // Delete related items and stocks
            PurchaseInventoryItem::where('purchase_inventory_id', $id)->delete();
            InventoryStock::where('purchase_inventory_id', $id)->delete();

            // Delete main purchase inventory record
            $purchaseInventory->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getStoredRecords()
    {
        return DB::table('inventory_stocks')
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_stocks.inventory_item_id')
            ->select(
                'inventory_stocks.inventory_item_id',
                'inventory_items.title as inventory_title',
                DB::raw('SUM(CASE WHEN inventory_stocks.purchase_inventory_id IS NOT NULL THEN inventory_stocks.qty ELSE 0 END) - SUM(CASE WHEN inventory_stocks.sales_id IS NOT NULL THEN inventory_stocks.qty ELSE 0 END) as net_qty')
            )
            ->groupBy('inventory_stocks.inventory_item_id', 'inventory_items.title')
            ->get();
    }

    public function getPurchaseRecords($inventoryItemId)
    {
        return DB::table('purchase_inventory_items')
            ->join('purchase_inventory', 'purchase_inventory.id', '=', 'purchase_inventory_items.purchase_inventory_id')
            ->join('inventory_items', 'inventory_items.id', '=', 'purchase_inventory_items.inventory_item_id')
            ->where('purchase_inventory_items.inventory_item_id', $inventoryItemId)
            ->select(
                'inventory_items.title as inventory_title',
                'purchase_inventory.bill_date as purchase_date',
                'purchase_inventory_items.rate',
                'purchase_inventory_items.qty'
            )
            ->get();
    }

    public function getSalesRecords($inventoryItemId)
    {
        return DB::table('inventory_stocks')
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_stocks.inventory_item_id')
            ->where('inventory_stocks.inventory_item_id', $inventoryItemId)
            ->where('inventory_stocks.sales_id', '!=', null)
            ->select(
                'inventory_items.title as inventory_title',
                'inventory_stocks.qty',
                'inventory_stocks.created_at'
            )->orderBy('created_at', 'desc')
            ->get();
    }
}
