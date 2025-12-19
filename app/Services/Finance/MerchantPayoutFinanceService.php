<?php

namespace App\Services\Finance;

use App\Models\MerchantPayout;
use Illuminate\Support\Facades\DB;
use LogicException;

class MerchantPayoutFinanceService
{
    public function __construct(
        private WalletService $wallets
    ) {}

    /**
     * Confirm merchant payout (money leaves system)
     */
    public function confirm(MerchantPayout $payout, ?int $actorUserId = null): void
    {
        // ✅ Guard
        if ($payout->status !== MerchantPayout::STATUS_OPEN) {
            throw new LogicException('Only open payouts can be confirmed.');
        }

        $amount = (float) $payout->total_amount;

        if ($amount <= 0) {
            throw new LogicException('Payout amount must be greater than zero.');
        }

        DB::transaction(function () use ($payout, $amount, $actorUserId) {

            $merchantWallet = $payout->merchant
                ->wallet()
                ->firstOrFail();

            /**
             * 💸 Merchant payout
             * Money leaves the system
             */
            $this->wallets->debit(
                wallet: $merchantWallet,
                amount: $amount,
                type: FinanceTypes::MERCHANT_PAYOUT_OUT,
                title: 'Merchant payout',
                notes: 'Merchant received payout (money left system)',
                shipmentId: null,
                reference: $payout,
                actorUserId: $actorUserId
            );

            $payout->update([
                'status'  => MerchantPayout::STATUS_PAID,
                'paid_at' => now(),
                'paid_by' => $actorUserId,
            ]);
        });
    }
}
