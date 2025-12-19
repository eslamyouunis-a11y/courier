<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'governorate_id',
        'manager_name',
        'manager_phone',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($branch) {
            $latest = static::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $branch->code = 'BR-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        });
    }

    /* =====================
     | العلاقات (Relationships)
     ===================== */

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    // المحفظة (Polymorphic)
    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'owner');
    }

    // الشحنات التابعة للفرع (مهم جداً للداشبورد)
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    // المناديب التابعين للفرع
    public function couriers(): HasMany
    {
        return $this->hasMany(Courier::class);
    }
}
