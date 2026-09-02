<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DealSection extends Model
{
    use HasFactory;

    /** Icons the trust bar may use, keyed by the value stored in the JSON. */
    public const TRUST_ICONS = [
        'banknote' => 'Banknote',
        'shield' => 'Shield',
        'badge-check' => 'Badge check',
        'headset' => 'Headset',
        'truck' => 'Truck',
        'star' => 'Star',
    ];

    protected $table = 'deal_sections';

    protected $fillable = [
        'heading',
        'subheading',
        'image',
        'image_alt',
        'trust_items',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'trust_items' => 'array',
        'status' => 'boolean',
    ];

    public function cards(): HasMany
    {
        return $this->hasMany(DealCard::class)->orderBy('sort_order')->orderBy('id');
    }

    /** The active one - what the storefront shows. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
