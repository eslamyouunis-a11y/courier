<?php

namespace App\Services\Finance;

use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use App\Services\Finance\WalletService;
use App\Services\Finance\FinanceTypes;
use App\Services\Shipping\ShippingFeeCalculator;

class ReturnToSenderFinanceService
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

            /** -------------------------
             * Load relations
             * -------------------------- */
            $merchant = $shipment->merchant()->firstOrFail();
            $courier  = $shipment->courier()->firstOrFail();
            $branch   = $shipment->branch()->firstOrFail(); // تم تحميله للاحتياط

            /** -------------------------
             * Calculate shipping fees (NEW ENGINE)
             * -------------------------- */

            // هنا نحسب تكلفة "المرتجع" تحديداً (Type: return)
            // لأن سعر المرتجع قد يختلف عن سعر التوصيل
            $shippingFees = $this->shippingCalculator->calculate(
                fromGovernorateId: $shipment->from_governorate_id,
                toGovernorateId: $shipment->to_governorate_id,
                type: 'return',
                merchantId: $shipment->merchant_id,
                areaId: $shipment->area_id
            );

            // تحديث قيمة الشحن في الشحنة لتوافق عملية الإرجاع
            $shipment->update([
                'shipping_fees' => $shippingFees,
            ]);

            /** -------------------------
             * Merchant charge
             * -------------------------- */

            $percentage = (float) $merchant->return_shipping_percentage;
            $merchantCharge = round($shippingFees * ($percentage / 100), 2);

            /** -------------------------
             * Courier commission
             * -------------------------- */

            $courierCommission = (float) $courier->commission_returned_sender;

            /** -------------------------
             * Merchant charged shipping
             * -------------------------- */
            if ($merchantCharge > 0) {
                $this->wallets->debit(
                    wallet: $merchant->wallet,
                    amount: $merchantCharge,
                    type: FinanceTypes::MERCHANT_SHIPPING_FEE_CHARGE,
                    title: 'Shipping fee on return',
                    notes: "Return fees: $shippingFees (Merchant bears $percentage%)",
                    shipmentId: $shipment->id,
                    reference: $shipment,
                    actorUserId: $actorUserId
                );
            }

            /** -------------------------
             * Company earns shipping
             * -------------------------- */
            if ($merchantCharge > 0) {
                $this->wallets->credit(
                    wallet: $this->wallets->getCompanyWallet(),
                    amount: $merchantCharge,
                    type: FinanceTypes::COMPANY_SHIPPING_FEE_INCOME,
                    title: 'Shipping fee income',
                    notes: "Income from returned shipment #{$shipment->id}",
                    shipmentId: $shipment->id,
                    reference: $shipment,
                    actorUserId: $actorUserId
                );
            }

            /** -------------------------
             * Courier commission (returned sender)
             * -------------------------- */
            if ($courierCommission > 0) {
                $this->wallets->credit(
                    wallet: $courier->wallet,
                    amount: $courierCommission,
                    type: FinanceTypes::COURIER_COMMISSION_RETURNED_SENDER,
                    title: 'Courier commission (returned sender)',
                    notes: null,
                    shipmentId: $shipment->id,
                    reference: $shipment,
                    actorUserId: $actorUserId
                );
            }
        });
    }
}
