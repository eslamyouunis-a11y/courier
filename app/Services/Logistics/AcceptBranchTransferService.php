<?php

namespace App\Services\Logistics;

use App\Models\BranchTransfer;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use LogicException;

class AcceptBranchTransferService
{
    public function accept(BranchTransfer $transfer): void
    {
        if ($transfer->status !== BranchTransfer::STATUS_PENDING) {
            throw new LogicException('Transfer is not pending.');
        }

        DB::transaction(function () use ($transfer) {

            foreach ($transfer->items as $item) {
                $shipment = $item->shipment;

                $shipment->update([
                    'branch_id'         => $transfer->to_branch_id,
                    'current_branch_id' => $transfer->to_branch_id,
                    'current_location'  => Shipment::LOCATION_BRANCH,
                    'sub_status'        => $shipment->status === Shipment::STATUS_RETURNED
                        ? Shipment::SUB_TRANSFERRED
                        : Shipment::SUB_IN_STOCK,
                ]);
            }

            $transfer->update([
                'status' => BranchTransfer::STATUS_ACCEPTED,
            ]);
        });
    }
}
