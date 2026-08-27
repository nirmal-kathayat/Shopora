<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $guarded = [];
    protected $table = 'inventory_items';

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
