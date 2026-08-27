<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $fillable = ['order_by', 'nepali_date', 'customer_id', 'discount', 'created_at', 'updated_at'];
    protected $guarded = [];
    protected $table = 'sales';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
