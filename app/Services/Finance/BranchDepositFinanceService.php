<?php

namespace App\Services\Finance;

use App\Models\BranchDeposit;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use LogicException;

class BranchDepositFinanceService
{
    public function confirm(
        BranchDeposit $deposit,
        ?int $actorUserId = null
    ): void {
        DB::transaction(function () use ($deposit, $actorUserId) {

            if ($deposit->status !== BranchDeposit::STATUS_OPEN) {
                throw new LogicException('Deposit is not open.');
            }

            $branchWallet = Wallet::where('owner_type', get_class($deposit->branch))
                ->where('owner_id', $deposit->branch_id)
                ->lockForUpdate()
                ->firstOrFail();

            $companyWallet = Wallet::where('owner_type', 'company')
                ->whereNull('owner_id')
                ->lockForUpdate()
                ->firstOrFail();

            if ($branchWallet->balance < $deposit->total_amount) {
                throw new LogicException('Insufficient branch balance.');
            }

            // 💸 Branch → Company
            app(WalletService::class)->debit(
                wallet: $branchWallet,
                amount: $deposit->total_amount,
                type: FinanceTypes::BRANCH_DEPOSIT_OUT,
                title: 'Branch deposit',
                notes: 'Deposit transferred to company',
                reference: $deposit,
                actorUserId: $actorUserId
            );

            app(WalletService::class)->credit(
                wallet: $companyWallet,
                amount: $deposit->total_amount,
                type: FinanceTypes::COMPANY_DEPOSIT_IN,
                title: 'Company deposit',
                notes: 'Received from branch deposit',
                reference: $deposit,
                actorUserId: $actorUserId
            );

            // ✅ Close mission
            $deposit->update([
                'status'      => BranchDeposit::STATUS_APPROVED,
                'approved_by' => $actorUserId,
                'approved_at' => now(),
            ]);
        });
    }
}
