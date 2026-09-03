<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSection extends Model
{
    use HasFactory;

    protected $table = 'hero_sections';

    protected $fillable = [
        'badge_text',
        'heading',
        'subheading',
        'author_name',
        'author_image',
        'primary_label',
        'primary_url',
        'secondary_label',
        'secondary_url',
        'image',
        'image_alt',
        'popular_searches',
        'delivery_title',
        'delivery_subtitle',
        'trust_label',
        'trust_value',
        'trust_subtitle',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'popular_searches' => 'array',
        'status' => 'boolean',
    ];

    /** The active one - what the storefront shows. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
