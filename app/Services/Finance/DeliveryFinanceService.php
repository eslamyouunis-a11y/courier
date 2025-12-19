<?php

namespace App\Services\Finance;

use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use App\Services\Finance\WalletService;
use App\Services\Finance\FinanceTypes;

class DeliveryFinanceService
{
    public function __construct(
        private WalletService $wallets
    ) {}

    /**
     * عند تسليم الشحنة (Delivered):
     * المنطق الجديد: الفلوس تدخل "عهدة" مع المندوب فقط.
     */
    public function onDelivered(
        Shipment $shipment,
        ?int $actorUserId = null
    ): void {
        DB::transaction(function () use ($shipment, $actorUserId) {

            $courier       = $shipment->courier()->firstOrFail();
            $courierWallet = $courier->wallet()->firstOrFail();

            $codAmount = (float) $shipment->amount; // المبلغ اللي استلمه المندوب من العميل
            $courierCommission = (float) $courier->commission_delivered; // عمولته عن المشوار

            /** -------------------------
             * 1. عهدة المندوب (المال في الحقيبة)
             * -------------------------- */
            $this->wallets->credit(
                wallet: $courierWallet,
                amount: $codAmount,
                type: FinanceTypes::COURIER_COD_ACCRUAL, // عهدة نقدية
                title: 'نقدية محصلة (عهدة)',
                notes: 'المبلغ طرف المندوب حالياً ولم يورد للفرع بعد',
                shipmentId: $shipment->id,
                reference: $shipment,
                actorUserId: $actorUserId
            );

            /** -------------------------
             * 2. استحقاق عمولة المندوب
             * -------------------------- */
            if ($courierCommission > 0) {
                $this->wallets->credit(
                    wallet: $courierWallet,
                    amount: $courierCommission,
                    type: FinanceTypes::COURIER_COMMISSION_DELIVERED,
                    title: 'عمولة توصيل',
                    notes: 'حق المندوب في العمولة (تضاف لرصيده القابل للسحب)',
                    shipmentId: $shipment->id,
                    reference: $shipment,
                    actorUserId: $actorUserId
                );
            }

            // 🛑 ملاحظة: تم إيقاف حركات الفرع والتاجر هنا.
            // لن يتم تحريك رصيد التاجر أو خزنة الفرع إلا في سيرفس الـ Handover.
        });
    }
}
