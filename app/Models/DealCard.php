<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealCard extends Model
{
    use HasFactory;

    /**
     * Icons a card may carry. The value also picks the built-in artwork when
     * the shop has not uploaded an image of its own.
     */
    public const ICONS = [
        'tag' => 'Tag (offer)',
        'truck' => 'Truck (delivery)',
        'package' => 'Package (restock)',
        'percent' => 'Percent (discount)',
        'gift' => 'Gift (bundle)',
        'bolt' => 'Bolt (flash sale)',
    ];

    protected $table = 'deal_cards';

    protected $fillable = [
        'deal_section_id',
        'badge_text',
        'title',
        'description',
        'cta_label',
        'cta_url',
        'icon',
        'image',
        'image_alt',
        'featured',
        'sort_order',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(DealSection::class, 'deal_section_id');
    }
}
