<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Customer $customer) {
            if ($customer->orders()->exists()) {
                throw new \DomainException("Cannot delete customer #{$customer->id} because they have existing orders.");
            }
        });
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<TradeInValuation, $this>
     */
    public function tradeInValuations(): HasMany
    {
        return $this->hasMany(TradeInValuation::class);
    }
}
