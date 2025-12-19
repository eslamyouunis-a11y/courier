<?php

namespace Tests\Feature\Logistics;

use App\Models\Branch;
use App\Models\Merchant;
use App\Models\Shipment;
use App\Models\MerchantReturnMission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\Logistics\BranchTransferService;
use LogicException;

class BranchTransferServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_marks_shipments_as_transferred_and_sets_transfer_fields(): void
    {
        $from = Branch::factory()->create();
        $to   = Branch::factory()->create();

        $merchant = Merchant::factory()->create([
            'branch_id' => $from->id,
        ]);

        $s1 = Shipment::factory()->create([
            'merchant_id' => $merchant->id,
            'branch_id'   => $from->id,
            'status'      => Shipment::STATUS_IN_PROGRESS,
            'sub_status'  => Shipment::SUB_IN_STOCK,
            'current_location' => Shipment::LOCATION_BRANCH,
            'current_courier_id' => null,
        ]);

        $s2 = Shipment::factory()->create([
            'merchant_id' => $merchant->id,
            'branch_id'   => $from->id,
            'status'      => Shipment::STATUS_RETURNED,
            'sub_status'  => Shipment::SUB_IN_STOCK,
            'current_location' => Shipment::LOCATION_BRANCH,
            'current_courier_id' => null,
        ]);

        app(BranchTransferService::class)->send([$s1, $s2], $from, $to);

        $s1->refresh();
        $s2->refresh();

        $this->assertEquals(Shipment::SUB_TRANSFERRED, $s1->sub_status);
        $this->assertEquals(Shipment::LOCATION_BRANCH, $s1->current_location);
        $this->assertNull($s1->current_courier_id);
        $this->assertEquals($from->id, $s1->transfer_from_branch_id);
        $this->assertEquals($to->id, $s1->transfer_to_branch_id);
        $this->assertEquals($from->id, $s1->branch_id);

        $this->assertEquals(Shipment::SUB_TRANSFERRED, $s2->sub_status);
        $this->assertEquals($from->id, $s2->transfer_from_branch_id);
        $this->assertEquals($to->id, $s2->transfer_to_branch_id);
    }

    public function test_send_blocks_delivered_shipments(): void
    {
        $from = Branch::factory()->create();
        $to   = Branch::factory()->create();

        $shipment = Shipment::factory()->create([
            'branch_id' => $from->id,
            'status'    => Shipment::STATUS_DELIVERED,
            'sub_status'=> Shipment::SUB_IN_STOCK,
            'current_location' => Shipment::LOCATION_BRANCH,
            'current_courier_id' => null,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Delivered shipment cannot be transferred.');

        app(BranchTransferService::class)->send([$shipment], $from, $to);
    }

    public function test_send_blocks_shipments_with_courier(): void
    {
        $from = Branch::factory()->create();
        $to   = Branch::factory()->create();

        $shipment = Shipment::factory()->create([
            'branch_id' => $from->id,
            'status'    => Shipment::STATUS_IN_PROGRESS,
            'sub_status'=> Shipment::SUB_WITH_COURIER,
            'current_location' => Shipment::LOCATION_COURIER,
            'current_courier_id' => 1,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Shipment with courier cannot be transferred.');

        app(BranchTransferService::class)->send([$shipment], $from, $to);
    }

    public function test_send_blocks_returned_shipments_assigned_to_mission(): void
    {
        $from = Branch::factory()->create();
        $to   = Branch::factory()->create();

        $mission = MerchantReturnMission::factory()->create();

        $shipment = Shipment::factory()->create([
            'branch_id' => $from->id,
            'status'    => Shipment::STATUS_RETURNED,
            'sub_status'=> Shipment::SUB_IN_STOCK,
            'current_location' => Shipment::LOCATION_BRANCH,
            'current_courier_id' => null,
            'merchant_return_mission_id' => $mission->id,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Returned shipment assigned to mission cannot be transferred.');

        app(BranchTransferService::class)->send([$shipment], $from, $to);
    }

    public function test_send_blocks_shipments_not_belonging_to_source_branch(): void
    {
        $from = Branch::factory()->create();
        $to   = Branch::factory()->create();
        $other = Branch::factory()->create();

        $shipment = Shipment::factory()->create([
            'branch_id' => $other->id,
            'status'    => Shipment::STATUS_IN_PROGRESS,
            'sub_status'=> Shipment::SUB_IN_STOCK,
            'current_location' => Shipment::LOCATION_BRANCH,
            'current_courier_id' => null,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Shipment does not belong to source branch.');

        app(BranchTransferService::class)->send([$shipment], $from, $to);
    }

    public function test_send_blocks_already_transferred_shipments(): void
    {
        $from = Branch::factory()->create();
        $to   = Branch::factory()->create();

        $shipment = Shipment::factory()->create([
            'branch_id' => $from->id,
            'status'    => Shipment::STATUS_IN_PROGRESS,
            'sub_status'=> Shipment::SUB_TRANSFERRED,
            'current_location' => Shipment::LOCATION_BRANCH,
            'current_courier_id' => null,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Shipment already in transfer.');

        app(BranchTransferService::class)->send([$shipment], $from, $to);
    }
}
