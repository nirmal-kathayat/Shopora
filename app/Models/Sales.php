<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sales extends Model
{
    protected $fillable = [
        'order_by',
        'nepali_date',
        'customer_id',
        'discount',
        'created_at',
        'updated_at',
        // A storefront order fills these; a counter sale leaves them at their
        // defaults. $fillable wins over $guarded, so they have to be listed.
        'status',
        'channel',
        'delivery_recipient',
        'delivery_phone',
        'delivery_address',
        'delivery_landmark',
        'delivery_fee',
    ];
    protected $guarded = [];
    protected $table = 'sales';

    protected $casts = ['delivery_fee' => 'decimal:2'];

    /** Where a storefront order can go from where it is now. */
    public const FLOW = [
        'placed' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipped', 'cancelled'],
        'shipped' => ['delivered', 'cancelled'],
        'delivered' => [],
        'cancelled' => [],
    ];

    /** A cancelled order puts its units back; a delivered one is done. */
    public const OPEN_STATUSES = ['placed', 'confirmed', 'shipped'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(SalesProduct::class, 'sales_id');
    }

    public function scopeStorefront(Builder $query): Builder
    {
        return $query->where('channel', 'storefront');
    }

    /**
     * A cancelled order is not a sale: it took no money and its units went
     * back on the shelf, so revenue, quantities and cost leave it out.
     */
    public function scopeCounted(Builder $query): Builder
    {
        return $query->where('sales.status', '!=', 'cancelled');
    }

    /**
     * What the customer sees on their order: ORD-2026-0012. Derived from the
     * id, so there is no second number to keep in step.
     */
    public function getCodeAttribute(): string
    {
        return 'ORD-' . ($this->created_at?->format('Y') ?? date('Y')) . '-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }
}
