<?php

namespace App\Services\Logistics;

use App\Models\BranchTransfer;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use LogicException;

class CancelBranchTransferService
{
    public function cancel(BranchTransfer $transfer): void
    {
        if ($transfer->status !== BranchTransfer::STATUS_PENDING) {
            throw new LogicException('Only pending transfers can be canceled.');
        }

        DB::transaction(function () use ($transfer) {

            foreach ($transfer->items as $item) {
                $shipment = $item->shipment;

                $shipment->update([
                    'current_location' => Shipment::LOCATION_BRANCH,
                    'sub_status'       => Shipment::SUB_IN_STOCK,
                ]);
            }

            $transfer->update([
                'status' => BranchTransfer::STATUS_CANCELED,
            ]);
        });
    }
}
