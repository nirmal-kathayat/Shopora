<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    protected $guarded = [];
    protected $table = 'inventory_stocks';

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function purchaseInventory()
    {
        return $this->belongsTo(PurchaseInventory::class);
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }
}
