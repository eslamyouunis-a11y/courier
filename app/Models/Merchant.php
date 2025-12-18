<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Merchant extends Model
{
    use HasFactory;

    /**
     * الحقول القابلة للتعبئة
     * ضفنا فيها الكود والمحافظ والربط بالفرع
     */
    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'cod_balance',      // محفظة المستحقات (فلوس الطرود المسلمة)
        'prepaid_balance',  // محفظة الشحن المسبق (رصيد شحن)
        'branch_id',        // الربط الإلزامي بالفرع
        'is_active',
    ];

    /**
     * العمليات التي تتم تلقائياً عند التعامل مع الموديل
     */
    protected static function boot()
    {
        parent::boot();

        // توليد كود التاجر تلقائياً بتنسيق MCH-0001 عند الإنشاء
        static::creating(function ($merchant) {
            $latest = static::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $merchant->code = 'MCH-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * العلاقة: التاجر ينتمي لفرع واحد أساسي
     * (حل مشكلة التنبيه الأحمر في الـ Resource)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
