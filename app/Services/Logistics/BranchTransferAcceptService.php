<?php

namespace App\Services\Logistics;

use App\Models\Branch;
use App\Models\Shipment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

class BranchTransferAcceptService
{
    /**
     * @param Collection|array $shipments
     */
    public function accept(
        Branch $branch,
        Collection|array $shipments
    ): void {
        DB::transaction(function () use ($branch, $shipments) {

            foreach ($shipments as $shipment) {

                /** =========================
                 * Guards
                 * ========================= */

                // ❌ مش محولة
                if ($shipment->sub_status !== Shipment::SUB_TRANSFERRED) {
                    throw new LogicException('Shipment is not transferred.');
                }

                // ❌ مش على الفرع ده
                if ($shipment->branch_id !== $branch->id) {
                    throw new LogicException('Shipment does not belong to this branch.');
                }

                // ❌ معينة لمندوب
                if ($shipment->current_courier_id !== null) {
                    throw new LogicException('Shipment assigned to courier.');
                }

                /** =========================
                 * Accept
                 * ========================= */

                // قيد التوصيل
                if ($shipment->status === Shipment::STATUS_IN_PROGRESS) {
                    $shipment->update([
                        'current_location' => Shipment::LOCATION_BRANCH,
                        'sub_status'       => Shipment::SUB_IN_STOCK,
                    ]);
                }

                // مرتجعة
                if ($shipment->status === Shipment::STATUS_RETURNED) {
                    $shipment->update([
                        'current_location' => Shipment::LOCATION_BRANCH,
                        // تفضل SUB_TRANSFERRED لأنها في مخزن المرتجعات
                    ]);
                }
            }
        });
    }
}
