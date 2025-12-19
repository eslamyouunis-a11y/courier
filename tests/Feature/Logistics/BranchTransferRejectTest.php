<?php

namespace Tests\Feature\Logistics;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;

use App\Models\{
    Branch,
    Merchant,
    Shipment
};

use App\Services\Logistics\BranchTransferAcceptService;

class BranchTransferRejectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function branch_cannot_accept_shipment_that_is_not_transferred()
    {
        /**
         * ======================
         * Arrange
         * ======================
         */

        $branch = Branch::factory()->create();

        $merchant = Merchant::factory()->create([
            'branch_id' => $branch->id,
        ]);

        // ❌ شحنة قيد التوصيل لكن مش محولة
        $shipment = Shipment::factory()->create([
            'branch_id'        => $branch->id,
            'merchant_id'      => $merchant->id,
            'status'           => Shipment::STATUS_IN_PROGRESS,
            'sub_status'       => Shipment::SUB_IN_STOCK, // 👈 مش transferred
            'current_location' => Shipment::LOCATION_BRANCH,
            'current_courier_id' => null,
        ]);

        /**
         * ======================
         * Assert Exception
         * ======================
         */

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Shipment is not transferred.');

        /**
         * ======================
         * Act
         * ======================
         */

        app(BranchTransferAcceptService::class)->accept(
            $branch,
            [$shipment]
        );
    }
}
