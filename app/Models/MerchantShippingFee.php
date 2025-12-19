<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantShippingFee extends Model
{
    protected $guarded = [];

    /* =====================
     | Relationships
     ===================== */

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function fromGovernorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'from_governorate_id');
    }

    public function toGovernorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'to_governorate_id');
    }

    // ✅ دي الإضافة المهمة اللي كانت ناقصة
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
