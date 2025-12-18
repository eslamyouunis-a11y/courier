<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Courier extends Model
{
    protected $fillable = [
        'code',
        'name',
        'phone',
        'id_number',
        'cod_wallet',
        'commission_wallet',
        'branch_id',
        'is_active'
    ];

    /**
     * توليد كود المندوب تلقائياً عند الإنشاء
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($courier) {
            // جلب آخر مندوب مسجل للحصول على الرقم التالي
            $latestCourier = static::latest('id')->first();
            $nextId = $latestCourier ? $latestCourier->id + 1 : 1;

            // توليد كود بتنسيق CR-0001
            $courier->code = 'CR-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * العلاقة: المندوب ينتمي لفرع واحد
     * (هذه الدالة ستحل خطأ الـ TypeError الذي ظهر في الصورة)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
