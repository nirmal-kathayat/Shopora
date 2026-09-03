<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id',
        'label',
        'recipient_name',
        'ph_number',
        'city',
        'area',
        'street',
        'landmark',
        'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The address as one line - what a delivery slip, the profile card and the
     * POS all want, rather than five fields each of them has to join up.
     */
    public function getSingleLineAttribute(): string
    {
        return collect([$this->street, $this->area, $this->city])
            ->filter()
            ->implode(', ');
    }
}
