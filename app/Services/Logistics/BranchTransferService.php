<?php

namespace App\Services\Logistics;

use App\Models\Branch;
use App\Models\Shipment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

class BranchTransferService
{
    /**
     * إرسال شحنات لتحويل بين فروع (Pending)
     *
     * @param Collection|array $shipments
     */
    public function send(
        Collection|array $shipments,
        Branch $fromBranch,
        Branch $toBranch
    ): void {
        DB::transaction(function () use ($shipments, $fromBranch, $toBranch) {

            foreach ($shipments as $shipment) {

                /** =========================
                 * Guards (موانع التحويل)
                 * ========================= */

                // ❌ مسلمة = فلوس خرجت
                if ($shipment->status === Shipment::STATUS_DELIVERED) {
                    throw new LogicException('Delivered shipment cannot be transferred.');
                }

                // ❌ مع مندوب
                if ($shipment->current_courier_id !== null) {
                    throw new LogicException('Shipment with courier cannot be transferred.');
                }

                // ❌ مرتجع ومربوط بمهمة
                if (
                    $shipment->status === Shipment::STATUS_RETURNED &&
                    $shipment->merchant_return_mission_id !== null
                ) {
                    throw new LogicException('Returned shipment assigned to mission cannot be transferred.');
                }

                // ❌ مش في الفرع المرسل
                if ($shipment->branch_id !== $fromBranch->id) {
                    throw new LogicException('Shipment does not belong to source branch.');
                }

                // ❌ بالفعل في تحويل
                if ($shipment->sub_status === Shipment::SUB_TRANSFERRED) {
                    throw new LogicException('Shipment already in transfer.');
                }

                /** =========================
                 * Send transfer (PENDING)
                 * ========================= */

                $shipment->update([
                    // ❗ لا نغيّر branch_id
                    'sub_status'        => Shipment::SUB_TRANSFERRED,
                    'current_location' => Shipment::LOCATION_BRANCH,
                    'current_courier_id'=> null,

                    // نحتاج نعرف رايحة فين
                    'transfer_from_branch_id' => $fromBranch->id,
                    'transfer_to_branch_id'   => $toBranch->id,
                ]);
            }
        });
    }
}
