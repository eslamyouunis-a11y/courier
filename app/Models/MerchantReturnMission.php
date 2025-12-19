<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantReturnMission extends Model
{
    use HasFactory;

    protected $guarded = [];

    /* =====================================================
     | Statuses (مطابقة للـ DB)
     ===================================================== */
    const STATUS_OPEN      = 'open';
    const STATUS_CONFIRMED = 'confirmed';

    /* =====================================================
     | Relationships
     ===================================================== */

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            MerchantReturnMissionItem::class,
            'mission_id'
        );
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(
            Shipment::class,
            'merchant_return_mission_id'
        );
    }

    /* =====================================================
     | Helpers
     ===================================================== */

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }
}
