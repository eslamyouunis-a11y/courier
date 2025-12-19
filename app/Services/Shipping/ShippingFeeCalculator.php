<?php

namespace App\Services\Shipping;

use App\Models\ShippingFee;
use App\Models\AreaShippingFee;
use App\Models\MerchantShippingFee;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class ShippingFeeCalculator
{
    public function calculate(
        int $fromGovernorateId,
        int $toGovernorateId,
        string $type,
        ?int $merchantId = null,
        ?int $areaId = null
    ): float {

        // التأكد من نوع العملية
        if (!in_array($type, ['delivery', 'return', 'cancel'], true)) {
            throw new InvalidArgumentException("Invalid fee type: {$type}");
        }

        /** =============================================
         * 1️⃣ Merchant Override (تعديلاتنا هنا)
         * ============================================= */
        if ($merchantId) {
            // أ) الأولوية الأولى: هل التاجر محدد سعر للمنطقة دي بالذات؟
            if ($areaId) {
                $merchantAreaFee = MerchantShippingFee::query()
                    ->where('merchant_id', $merchantId)
                    ->where('area_id', $areaId) // بحث بالمنطقة
                    ->where('is_active', true)
                    ->first();

                if ($merchantAreaFee) {
                    return $this->resolveFee($merchantAreaFee, $type);
                }
            }

            // ب) الأولوية الثانية: هل التاجر محدد سعر للمحافظة عموماً؟
            $merchantGovFee = MerchantShippingFee::query()
                ->where('merchant_id', $merchantId)
                ->where('from_governorate_id', $fromGovernorateId)
                ->where('to_governorate_id', $toGovernorateId)
                ->whereNull('area_id') // تأكد إنه سعر عام مش لمنطقة تانية
                ->where('is_active', true)
                ->first();

            if ($merchantGovFee) {
                return $this->resolveFee($merchantGovFee, $type);
            }
        }

        /** =============================================
         * 2️⃣ Default Shipping Fee (سعر النظام للمحافظة)
         * ============================================= */
        // هنا بنجيب السعر الأساسي بين المحافظتين من إعدادات السيستم
        $shippingFee = ShippingFee::query()
            ->where('from_governorate_id', $fromGovernorateId)
            ->where('to_governorate_id', $toGovernorateId)
            ->where('is_active', true)
            ->first();

        // لو مفيش سعر سيستم أصلاً للمسار ده، نضرب Error عشان ننبه الإدارة
        if (!$shippingFee) {
             // ممكن ترجع 0 لو مش عايز توقف السيستم، بس الأفضل Exception عشان تكتشف المشكلة
            throw new ModelNotFoundException(
                "No shipping fee defined in system for route {$fromGovernorateId} → {$toGovernorateId}"
            );
        }

        /** =============================================
         * 3️⃣ Area Override (سعر النظام للمنطقة)
         * ============================================= */
        // لو السيستم نفسه حاطط سعر خاص لمنطقة (مثلاً مناطق نائية)
        if ($areaId) {
            $areaFee = AreaShippingFee::query()
                ->where('shipping_fee_id', $shippingFee->id)
                ->where('area_id', $areaId)
                ->where('is_active', true)
                ->first();

            if ($areaFee) {
                return $this->resolveFee($areaFee, $type);
            }
        }

        /** =============================================
         * 4️⃣ Default Province Fee (تطبيق سعر المحافظة العام)
         * ============================================= */
        return $this->resolveFee($shippingFee, $type);
    }

    /**
     * دالة مساعدة لحساب القيمة (ثابتة أو نسبة)
     */
    protected function resolveFee(object $model, string $type): float
    {
        $valueField = "{$type}_fee";       // delivery_fee, return_fee, ...
        $typeField  = "{$type}_fee_type";  // fixed, percent

        // التعامل مع الحالات التي قد لا يكون فيها الحقل موجوداً في الموديل
        $value = (float) ($model->{$valueField} ?? 0);
        $mode  = $model->{$typeField} ?? 'fixed';

        if ($mode === 'fixed') {
            return $value;
        }

        if ($mode === 'percent') {
            // في حالة النسبة، بنحسبها من سعر التوصيل الأساسي (delivery_fee) لنفس الموديل
            // إلا لو كان الموديل هو نفسه ShippingFee
            $baseAmount = (float) ($model->delivery_fee ?? 0);
            return round($baseAmount * ($value / 100), 2);
        }

        return $value;
    }
}
