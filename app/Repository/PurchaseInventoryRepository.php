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

    /** The purchase bill listing. */
    public function getPurchaseInventory(array $options = [])
    {
        $query = $this->query
            ->select(
                'purchase_inventory.id',
                'purchase_inventory.vendor as vendor_name',
                'purchase_inventory.bill_date as purchase_date',
                'purchase_inventory.vat_amount',
            );

        // The header-row box under Vendor Name.
        $vendor = trim((string) ($options['vendor_name'] ?? ''));
        if ($vendor !== '') {
            $query->where('purchase_inventory.vendor', 'like', '%' . $vendor . '%');
        }

        $search = trim((string) ($options['search'] ?? ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('purchase_inventory.vendor', 'like', $like)
                    ->orWhere('purchase_inventory.pan_number', 'like', $like)
                    ->orWhere('purchase_inventory.address', 'like', $like);
            });
        }

        return $this->sortListing($query, $options['sort_field'] ?? null, $options['sort_direction'] ?? null);
    }

    /**
     * Order the listing. By column name only - the field arrives in a query
     * string, and a column name is not something to take on trust.
     */
    private function sortListing($query, $field, $direction)
    {
        $sortable = [
            'vendor_name' => 'purchase_inventory.vendor',
            'purchase_date' => 'purchase_inventory.bill_date',
        ];

        $column = $sortable[$field] ?? null;
        if (! $column) {
            return $query->orderBy('purchase_inventory.id', 'desc');
        }

        return $query->orderBy($column, strtolower((string) $direction) === 'asc' ? 'asc' : 'desc');
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

            // Delete existing items and stocks. An item dropped from the bill
            // is not written back below, so its stock falls with nothing to
            // fire a model event - name them all before the rows go.
            $wasStocking = InventoryStock::where('purchase_inventory_id', $id)
                ->pluck('inventory_item_id');

            PurchaseInventoryItem::where('purchase_inventory_id', $id)->delete();
            InventoryStock::where('purchase_inventory_id', $id)->delete();

            app(StockAlertRepository::class)->checkLater($wasStocking);

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

            // Delete related items and stocks - see update() on why the items
            // are named before their rows are removed.
            $wasStocking = InventoryStock::where('purchase_inventory_id', $id)
                ->pluck('inventory_item_id');

            PurchaseInventoryItem::where('purchase_inventory_id', $id)->delete();
            InventoryStock::where('purchase_inventory_id', $id)->delete();

            app(StockAlertRepository::class)->checkLater($wasStocking);

            // Delete main purchase inventory record
            $purchaseInventory->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Units on hand per item: what purchases brought in, less what sales took
     * out. One row per item that has ever moved.
     *
     * Returns the query rather than the rows, so the screen can page it.
     */
    public function getStoredRecords(array $options = [])
    {
        $query = DB::table('inventory_stocks')
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_stocks.inventory_item_id')
            ->select(
                'inventory_stocks.inventory_item_id',
                'inventory_items.title as inventory_title',
                DB::raw('SUM(CASE WHEN inventory_stocks.purchase_inventory_id IS NOT NULL THEN inventory_stocks.qty ELSE 0 END) - SUM(CASE WHEN inventory_stocks.sales_id IS NOT NULL THEN inventory_stocks.qty ELSE 0 END) as net_qty')
            )
            ->groupBy('inventory_stocks.inventory_item_id', 'inventory_items.title');

        // Both the header-row box and the search box ask the same question of
        // the only text column there is, so they narrow the same way.
        foreach (['inventory_title', 'search'] as $key) {
            $value = trim((string) ($options[$key] ?? ''));
            if ($value !== '') {
                $query->where('inventory_items.title', 'like', '%' . $value . '%');
            }
        }

        return $this->sortRecords($query, $options['sort_field'] ?? null, $options['sort_direction'] ?? null);
    }

    /** Order the records. By column name only - the field arrives in a URL. */
    private function sortRecords($query, $field, $direction)
    {
        $sortable = ['inventory_title' => 'inventory_items.title', 'net_qty' => 'net_qty'];
        $column = $sortable[$field] ?? null;

        if (! $column) {
            return $query->orderBy('inventory_items.title');
        }

        return $query->orderBy($column, strtolower((string) $direction) === 'asc' ? 'asc' : 'desc');
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
