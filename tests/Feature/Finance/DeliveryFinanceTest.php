<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Branch;
use App\Models\Courier;
use App\Models\Merchant;
use App\Models\Shipment;
use App\Models\Wallet;
use App\Services\Finance\DeliveryFinanceService;
use App\Services\Finance\WalletService;
use App\Services\Finance\FinanceTypes;

class DeliveryFinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivered_shipment_creates_correct_financial_movements()
    {
        /**
         * -------------------------------------------------
         * Arrange
         * -------------------------------------------------
         */

        // Branch
        $branch = Branch::factory()->create();
        Wallet::factory()->forOwner($branch)->create();

        // Courier
        $courier = Courier::factory()->create([
            'branch_id' => $branch->id,
            'commission_delivered' => 20, // عمولة التسليم
        ]);
        Wallet::factory()->forOwner($courier)->create();

        // Merchant
        $merchant = Merchant::factory()->create([
            'branch_id' => $branch->id,
        ]);
        Wallet::factory()->forOwner($merchant)->create();

        // Shipment
        $shipment = Shipment::factory()->create([
            'branch_id'   => $branch->id,
            'courier_id'  => $courier->id,
            'merchant_id' => $merchant->id,
            'amount'      => 1000, // COD
            'shipping_fees' => 60,
            'status'      => Shipment::STATUS_DELIVERED,
        ]);

        $codAmount        = 1000;
        $courierCommission= 20;
        $merchantEarning  = 940; // 1000 - 60

        $service = new DeliveryFinanceService(new WalletService());

        /**
         * -------------------------------------------------
         * Act
         * -------------------------------------------------
         */
        $service->onDelivered(
    $shipment,
    $codAmount,
    $courierCommission,
    $merchantEarning
);


        /**
         * -------------------------------------------------
         * Assert
         * -------------------------------------------------
         */

        // Reload wallets
        $courierWallet  = $courier->wallet()->first();
        $branchWallet   = $branch->wallet()->first();
        $merchantWallet = $merchant->wallet()->first();

        // Courier wallet
        $this->assertEquals(
            1020,
            $courierWallet->balance,
            'Courier wallet should contain COD + commission'
        );

        // Branch wallet (total responsibility)
        $this->assertEquals(
            1000,
            $branchWallet->balance,
            'Branch wallet total should increase by COD'
        );

        // Merchant wallet (earning, not yet withdrawn)
        $this->assertEquals(
            940,
            $merchantWallet->balance,
            'Merchant wallet should contain earning after shipping fees'
        );

        // Check ledger entries count (sanity)
        $this->assertDatabaseCount('wallet_transactions', 4);
    }
}
