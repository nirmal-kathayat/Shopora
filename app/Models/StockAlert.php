<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One customer waiting for one product to come back.
 *
 * Written by the storefront's "Notify me" button, read by StockAlertRepository
 * when the units land, and deleted the moment the customer has been told - a
 * standing request, not a history.
 */
class StockAlert extends Model
{
    protected $guarded = [];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
