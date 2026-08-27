<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInventory extends Model
{
    protected $guarded = [];
    protected $table = 'purchase_inventory';

    protected $casts = [
        'bill_date' => 'date',
        'vat_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseInventoryItem::class);
    }

    public function stocks()
    {
        return $this->hasMany(InventoryStock::class);
    }
}
