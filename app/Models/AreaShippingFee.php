<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AreaShippingFee extends Model
{
    /**
     * الحقول المسموح بحفظها (Mass Assignment).
     * ضرورية لضمان حفظ القيم المالية والأنواع بشكل صحيح.
     */
    protected $fillable = [
        'shipping_fee_id',   // المسار الأصلي (من محافظة -> إلى محافظة)
        'area_id',           // المنطقة المخصصة
        'delivery_fee',      // سعر توصيل المنطقة
        'delivery_fee_type', // نوع السعر (fixed/percent)
        'return_fee',        // سعر المرتجع
        'return_fee_type',   // نوع سعر المرتجع
        'cancel_fee',        // سعر الإلغاء
        'cancel_fee_type',   // نوع سعر الإلغاء
        'is_active',         // الحالة
    ];

    /* =====================
     | Relationships
     ===================== */

    /**
     * العلاقة مع تسعيرة المحافظة الأم (المسار).
     */
    public function shippingFee(): BelongsTo
    {
        return $this->belongsTo(ShippingFee::class);
    }

    /**
     * العلاقة مع المنطقة الجغرافية.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
