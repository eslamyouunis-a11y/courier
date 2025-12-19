<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingFee extends Model
{
    /**
     * الحقول المسموح بحفظها (Mass Assignment).
     */
    protected $fillable = [
        'from_governorate_id',
        'to_governorate_id',
        'delivery_fee',      // العمود المالي الأساسي للتوصيل
        'delivery_fee_type', // نوع رسوم التوصيل (fixed/percent)
        'return_fee',        // رسوم المرتجع
        'return_fee_type',   // نوع رسوم المرتجع
        'cancel_fee',        // رسوم الإلغاء
        'cancel_fee_type',   // نوع رسوم الإلغاء
        'is_active',         // حالة النشاط
    ];

    /* =====================
     | Relationships
     ===================== */

    /**
     * العلاقة مع محافظة المصدر.
     */
    public function fromGovernorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'from_governorate_id');
    }

    /**
     * العلاقة مع محافظة الوجهة.
     */
    public function toGovernorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'to_governorate_id');
    }

    /**
     * العلاقة مع تخصيصات المناطق (Area Overrides).
     * هذه العلاقة ضرورية لعمل Relation Manager في Filament.
     */
    public function areaShippingFees(): HasMany
    {
        return $this->hasMany(AreaShippingFee::class);
    }
}
