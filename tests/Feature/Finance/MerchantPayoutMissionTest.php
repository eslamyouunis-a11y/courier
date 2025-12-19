<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\{
    Merchant,
    Wallet,
    MerchantPayout
};

use App\Services\Finance\MerchantPayoutFinanceService;
use App\Services\Finance\FinanceTypes;

class MerchantPayoutMissionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function merchant_payout_moves_money_out_of_system()
    {
        /**
         * ======================
         * Arrange
         * ======================
         */

        $merchant = Merchant::factory()->create();

        $merchantWallet = Wallet::factory()->create([
            'owner_type' => Merchant::class,
            'owner_id'   => $merchant->id,
            'balance'    => 5000,
        ]);

        $payout = MerchantPayout::create([
            'merchant_id'  => $merchant->id,
            'total_amount' => 2000,
            'status'       => MerchantPayout::STATUS_OPEN,
        ]);

        /**
         * ======================
         * Act
         * ======================
         */

        app(MerchantPayoutFinanceService::class)
            ->confirm($payout);

        /**
         * ======================
         * Assert
         * ======================
         */

        $merchantWallet->refresh();
        $payout->refresh();

        // 💸 خصم من محفظة التاجر
        $this->assertEquals(3000, $merchantWallet->balance);

        // ✅ المهمة اتقفلت
        $this->assertEquals(
            MerchantPayout::STATUS_PAID,
            $payout->status
        );

        // 🧾 Ledger: حركة واحدة فقط
        $this->assertDatabaseHas('wallet_transactions', [
            'type'      => FinanceTypes::MERCHANT_PAYOUT_OUT,
            'direction' => 'debit',
        ]);

        // ❌ لا أي حركة شركة
        $this->assertDatabaseMissing('wallet_transactions', [
            'type' => FinanceTypes::COMPANY_PAYOUT_OUT,
        ]);
    }
}
