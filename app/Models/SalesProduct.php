<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesProduct extends Model
{
    protected $guarded = [];
    protected $table = 'sales_products';

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'product_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }
}
