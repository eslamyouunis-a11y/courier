<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $guarded = [];

    // --- الحالات الأساسية (Status) ---
    const STATUS_SAVED = 'saved';             // محفوظة (قبل القبول في الفرع)
    const STATUS_IN_PROGRESS = 'in_progress';   // قيد التنفيذ (تم القبول)
    const STATUS_DELIVERED = 'delivered';       // تم التسليم بنجاح
    const STATUS_RETURNED = 'returned';         // مرتجعة

    // --- الحالات الفرعية (Sub Status) ---
    const SUB_IN_STOCK = 'in_stock';            // في المخزن (بعد القبول)
    const SUB_ASSIGNED = 'assigned';            // معينة لكابتن (تحت التجهيز)
    const SUB_WITH_COURIER = 'with_courier';    // مسلمة لكابتن (خرجت للتوصيل)
    const SUB_DEFERRED = 'deferred';            // مؤجلة مع الكابتن
    const SUB_TRANSFERRED = 'transferred';      // محولة من فرع لآخر

    // --- علاقات الأطراف الأساسية ---
    public function merchant(): BelongsTo { return $this->belongsTo(Merchant::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); } // فرع المنشأ
    public function courier(): BelongsTo { return $this->belongsTo(Courier::class); } // المندوب المخصص
    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function area(): BelongsTo { return $this->belongsTo(Area::class); }

    // --- علاقات التتبع الحالي (Current Path) ---
    public function current_branch(): BelongsTo { return $this->belongsTo(Branch::class, 'current_branch_id'); }
    public function current_courier(): BelongsTo { return $this->belongsTo(Courier::class, 'current_courier_id'); }

    // --- لوجيك التأسيس (Boot Method) ---
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shipment) {
            // 1. توليد رقم تتبع أرقام فقط (مثال: 35748634)
            if (empty($shipment->tracking_number)) {
                $shipment->tracking_number = (string) mt_rand(10000000, 99999999);
            }

            // 2. الحالة الافتراضية عند الإنشاء (قبل القبول)
            $shipment->status = self::STATUS_SAVED;

            // 3. حساب الإجمالي تلقائياً (سعر المنتج + مصاريف الشحن)
            $shipment->total_amount = ($shipment->amount ?? 0) + ($shipment->shipping_fees ?? 0);
        });

        static::updating(function ($shipment) {
            // تحديث الإجمالي دائماً عند تعديل أي مبالغ
            $shipment->total_amount = ($shipment->amount ?? 0) + ($shipment->shipping_fees ?? 0);
        });
    }

    // --- دوال مساعدة (Helpers) للحالات ---
    public function isSaved(): bool { return $this->status === self::STATUS_SAVED; }
    public function isInStock(): bool { return $this->sub_status === self::SUB_IN_STOCK; }
    public function isWithCourier(): bool { return $this->sub_status === self::SUB_WITH_COURIER; }
}
