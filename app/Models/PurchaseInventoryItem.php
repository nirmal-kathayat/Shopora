<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInventoryItem extends Model
{
    protected $guarded = [];
    protected $table = 'purchase_inventory_items';

    public function purchaseInventory()
    {
        return $this->belongsTo(PurchaseInventory::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
