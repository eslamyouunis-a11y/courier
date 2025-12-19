<?php

namespace App\Services\Finance;

use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use App\Services\Finance\WalletService;
use App\Services\Finance\FinanceTypes;
use App\Services\Shipping\ShippingFeeCalculator;

class CancelShipmentFinanceService
{
    public function __construct(
        private WalletService $wallets,
        private ShippingFeeCalculator $shippingCalculator
    ) {}

    public function handle(
        Shipment $shipment,
        ?int $actorUserId = null
    ): void {
        DB::transaction(function () use ($shipment, $actorUserId) {

            // Load relations
            $merchant = $shipment->merchant()->firstOrFail();

            // 1️⃣ حساب مصاريف "الإلغاء" (Type: cancel)
            // ممكن يكون للتاجر ده سعر إلغاء مخصص (مثلاً 10 جنيه) أو نسبة
            $cancellationFees = $this->shippingCalculator->calculate(
                fromGovernorateId: $shipment->from_governorate_id,
                toGovernorateId: $shipment->to_governorate_id,
                type: 'cancel', // 👈 النوع هنا إلغاء
                merchantId: $shipment->merchant_id,
                areaId: $shipment->area_id
            );

            // تحديث الشحنة بقيمة الإلغاء (عشان تبقى مرجع)
            $shipment->update([
                'shipping_fees' => $cancellationFees,
            ]);

            // 2️⃣ خصم رسوم الإلغاء من التاجر
            if ($cancellationFees > 0) {
                // خصم من محفظة التاجر
                $this->wallets->debit(
                    wallet: $merchant->wallet,
                    amount: $cancellationFees,
                    type: FinanceTypes::MERCHANT_SHIPPING_FEE_CHARGE, // أو ممكن تعمل نوع جديد MERCHANT_CANCEL_FEE
                    title: 'Cancellation Fee',
                    notes: "Fees for cancelled shipment #{$shipment->id}",
                    shipmentId: $shipment->id,
                    reference: $shipment,
                    actorUserId: $actorUserId
                );

                // إيراد للشركة
                $this->wallets->credit(
                    wallet: $this->wallets->getCompanyWallet(),
                    amount: $cancellationFees,
                    type: FinanceTypes::COMPANY_SHIPPING_FEE_INCOME,
                    title: 'Cancellation Fee Income',
                    notes: null,
                    shipmentId: $shipment->id,
                    reference: $shipment,
                    actorUserId: $actorUserId
                );
            }

            // ملحوظة: في الإلغاء غالباً مفيش عمولة للمندوب، إلا لو سياستك بتدي للمندوب "محاولة تسليم"
            // لو عايز تضيف عمولة محاولة تسليم، ممكن تزودها هنا بنفس طريقة المرتجع
        });
    }
}
