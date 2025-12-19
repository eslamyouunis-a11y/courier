<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Governorate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shipping_cost',
        'is_active',
    ];

    /**
     * المحافظة الواحدة لها مناطق كثيرة
     */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }
    public function shippingFeesFrom()
{
    return $this->hasMany(ShippingFee::class, 'from_governorate_id');
}

public function shippingFeesTo()
{
    return $this->hasMany(ShippingFee::class, 'to_governorate_id');
}

}
