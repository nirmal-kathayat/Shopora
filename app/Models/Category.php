<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * Icons a category may carry, keyed by the value stored on the row. The
     * storefront maps each to a matching lucide icon.
     */
    public const ICONS = [
        'grid' => 'Grid (default)',
        'monitor' => 'Monitor (electronics)',
        'basket' => 'Basket (grocery)',
        'pencil' => 'Pencil (stationery)',
        'hammer' => 'Hammer (hardware)',
        'cup' => 'Cup (beverages)',
        'sparkles' => 'Sparkles (personal care)',
        'shirt' => 'Shirt (clothing)',
        'baby' => 'Baby (kids)',
        'gamepad' => 'Gamepad (toys)',
        'pill' => 'Pill (health)',
        'home' => 'Home (household)',
    ];

    protected $table = 'categories';

    protected $fillable = [
        'title',
        'slug',
        'icon',
        'image',
        'image_alt',
        'status',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'category_id');
    }

    /** Shown on the storefront. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
