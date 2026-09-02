<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    /** Icons a feature highlight may use, keyed by the stored value. */
    public const HIGHLIGHT_ICONS = [
        'droplet' => 'Droplet',
        'droplets' => 'Droplets',
        'sparkles' => 'Sparkles',
        'leaf' => 'Leaf',
        'shield' => 'Shield',
        'heart' => 'Heart',
        'check' => 'Check',
        'star' => 'Star',
        'zap' => 'Bolt',
        'sun' => 'Sun',
        'feather' => 'Feather',
        'award' => 'Award',
    ];

    protected $guarded = [];
    protected $table = 'inventory_items';

    protected $casts = [
        'highlights' => 'array',
        'features' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }
}
