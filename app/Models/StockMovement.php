<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_condition_id',
        'type',
        'quantity',
        'note',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function productCondition(): BelongsTo
    {
        return $this->belongsTo(ProductCondition::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
