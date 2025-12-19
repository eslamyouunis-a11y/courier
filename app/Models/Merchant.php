<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'address',
        'branch_id',
        'governorate_id',
        'area_id',             // area_id بدل city_id
        'return_shipping_percentage',
        'settlement_days',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($merchant) {
            $latest = static::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $merchant->code = 'MCH-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        });
    }

    /* =====================
     | العلاقات (Relationships)
     ===================== */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'owner');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    // ✅ تم تعديل الاسم هنا ليطابق Filament Relation Manager
    // كان shippingFeeOverrides وأصبح merchantShippingFees
    public function merchantShippingFees(): HasMany
    {
        return $this->hasMany(MerchantShippingFee::class);
    }
}
