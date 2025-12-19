<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\Shipping\ShippingFeeCalculator;

class Shipment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    /* =====================================================
     | الحالة الأساسية (Lifecycle Status)
     ===================================================== */
    const STATUS_SAVED        = 'saved';
    const STATUS_IN_PROGRESS  = 'in_progress';
    const STATUS_DELIVERED    = 'delivered';   // فلوس خرجت
    const STATUS_RETURNED     = 'returned';

    /* =====================================================
     | الحالة الفرعية (Operational Sub Status)
     ===================================================== */
    const SUB_IN_STOCK     = 'in_stock';        // في مخزن الفرع
    const SUB_ASSIGNED     = 'assigned';        // متعينة لمندوب
    const SUB_WITH_COURIER = 'with_courier';    // مع مندوب
    const SUB_DEFERRED     = 'deferred';        // مؤجلة
    const SUB_TRANSFERRED  = 'transferred';     // تحويل (صادر / وارد / انتظار)

    /* =====================================================
     | مكان الشحنة (Custody)
     ===================================================== */
    const LOCATION_BRANCH  = 'branch';
    const LOCATION_COURIER = 'courier';

    /* =====================================================
     | العلاقات الأساسية
     ===================================================== */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /* =====================================================
     | المسار الحالي (Tracking)
     ===================================================== */
    public function currentBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'current_branch_id');
    }

    public function currentCourier(): BelongsTo
    {
        return $this->belongsTo(Courier::class, 'current_courier_id');
    }

    /* =====================================================
     | علاقات مالية / مهام
     ===================================================== */

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(\App\Models\WalletTransaction::class, 'shipment_id');
    }

    public function courierHandover(): BelongsTo
    {
        return $this->belongsTo(CourierHandover::class);
    }

    public function branchDeposit(): BelongsTo
    {
        return $this->belongsTo(BranchDeposit::class);
    }

    public function merchantPayout(): BelongsTo
    {
        return $this->belongsTo(MerchantPayout::class);
    }

    public function merchantReturnMission(): BelongsTo
    {
        return $this->belongsTo(MerchantReturnMission::class);
    }

    /* =====================================================
     | Boot Logic (Defaults + Integrity + Auto Calc)
     ===================================================== */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shipment) {

            // 1. Tracking number
            if (empty($shipment->tracking_number)) {
                $shipment->tracking_number = (string) mt_rand(10000000, 99999999);
            }

            // 2. Status defaults
            if (empty($shipment->status)) {
                $shipment->status = self::STATUS_SAVED;
            }

            if (empty($shipment->sub_status)) {
                $shipment->sub_status = self::SUB_IN_STOCK;
            }

            // 3. Location default
            if (empty($shipment->current_location)) {
                $shipment->current_location = self::LOCATION_BRANCH;
            }

            // 4. تعيين محافظة الراسل تلقائياً من الفرع المختارة لمنع الـ TypeError 🛡️
            if (empty($shipment->from_governorate_id) && $shipment->branch_id) {
                $shipment->from_governorate_id = $shipment->branch?->governorate_id;
            }

            // 5. ⚡ Auto Calculate Shipping Fees ⚡
            if (is_null($shipment->shipping_fees)) {
                // التحقق من وجود المحافظات قبل استدعاء الحاسبة
                if ($shipment->from_governorate_id && $shipment->governorate_id) {
                    try {
                        $calculator = app(ShippingFeeCalculator::class);

                        $fee = $calculator->calculate(
                            fromGovernorateId: (int) $shipment->from_governorate_id,
                            toGovernorateId: (int) $shipment->governorate_id,
                            type: 'delivery',
                            merchantId: $shipment->merchant_id,
                            areaId: $shipment->area_id
                        );

                        $shipment->shipping_fees = $fee;

                    } catch (\Exception $e) {
                        $shipment->shipping_fees = 0;
                    }
                } else {
                    // في حالة نقص البيانات نضع 0 لضمان استمرار العملية
                    $shipment->shipping_fees = 0;
                }
            }

            // 6. Cached total
            $shipment->total_amount =
                ($shipment->amount ?? 0) +
                ($shipment->shipping_fees ?? 0);
        });

        static::updating(function ($shipment) {
            $shipment->total_amount =
                ($shipment->amount ?? 0) +
                ($shipment->shipping_fees ?? 0);
        });
    }

    /* =====================================================
     | Helpers (Readable business checks)
     ===================================================== */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    public function isWithCourier(): bool
    {
        return $this->sub_status === self::SUB_WITH_COURIER;
    }

    public function isInBranchStock(): bool
    {
        return $this->current_location === self::LOCATION_BRANCH
            && in_array($this->sub_status, [
                self::SUB_IN_STOCK,
                self::SUB_DEFERRED,
                self::SUB_TRANSFERRED,
            ], true);
    }

    public function isPendingTransfer(): bool
    {
        return $this->sub_status === self::SUB_TRANSFERRED;
    }

    /* =====================================================
     | Business Rules
     ===================================================== */

    public function canBeTransferred(): bool
    {
        if ($this->isDelivered() || $this->isWithCourier()) {
            return false;
        }

        if ($this->merchant_return_mission_id !== null) {
            return false;
        }

        if ($this->current_location !== self::LOCATION_BRANCH) {
            return false;
        }

        return true;
    }
}
