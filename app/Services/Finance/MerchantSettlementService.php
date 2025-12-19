<?php

namespace App\Services\Finance;

use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\Finance\WalletService;
use App\Services\Finance\FinanceTypes;

class MerchantSettlementService
{
    public function __construct(
        private WalletService $wallets
    ) {}

    /**
     * بعد مرور مدة على التسليم:
     * تحويل حق التاجر من Total إلى Available
     *
     * - Merchant:
     *   - Total (يخرج)
     *   + Available (يدخل)
     */
    public function moveToAvailable(
        Shipment $shipment,
        float $amount,
        ?int $actorUserId = null
    ): void {
        DB::transaction(function () use ($shipment, $amount, $actorUserId) {

            $merchant = $shipment->merchant()->firstOrFail();
            $merchantWallet = $merchant->wallet()->firstOrFail();

            $now = Carbon::now()->toDateTimeString();

            // إخراج من إجمالي المستحق
            $this->wallets->debit(
                wallet: $merchantWallet,
                amount: $amount,
                type: FinanceTypes::MERCHANT_TOTAL_MOVE_OUT,
                title: 'Settlement move out',
                notes: 'Move from total to available (settled)',
                shipmentId: $shipment->id,
                reference: $shipment,
                actorUserId: $actorUserId,
                occurredAt: $now
            );

            // إدخال في القابل للتوريد
            $this->wallets->credit(
                wallet: $merchantWallet,
                amount: $amount,
                type: FinanceTypes::MERCHANT_AVAILABLE_MOVE_IN,
                title: 'Settlement available',
                notes: 'Now available for payout',
                shipmentId: $shipment->id,
                reference: $shipment,
                actorUserId: $actorUserId,
                occurredAt: $now
            );
        });
    }
}
