<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Area extends Model
{
    protected $fillable = ['governorate_id', 'name', 'override_shipping_cost', 'is_active'];

    // العلاقة: المنطقة تابعة لمحافظة واحدة
    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }
    public function shippingFees()
{
    return $this->hasMany(AreaShippingFee::class);
}

}
