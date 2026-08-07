<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeInValuation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'device_brand',
        'device_model',
        'condition_description',
        'estimated_value',
        'status',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
