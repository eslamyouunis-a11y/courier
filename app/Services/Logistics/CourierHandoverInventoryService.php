<?php

namespace App\Services\Logistics;

use App\Models\CourierHandover;
use App\Models\Shipment;

class CourierHandoverInventoryService
{
    public function handle(CourierHandover $handover): void
    {
        foreach ($handover->items as $item) {

            $shipment = $item->shipment;

            match ($item->item_type) {

                /**
                 * ✅ مسلمة
                 * - خرجت من التشغيل
                 * - مش في عهدة مندوب
                 * - مش بتدخل أي مخزن تشغيلي
                 */
                'delivered' => $shipment->update([
                    'current_location'   => Shipment::LOCATION_BRANCH,
                    'sub_status'         => null,
                    'current_courier_id' => null,
                ]),

                /**
                 * ✅ مؤجلة
                 * - تدخل مخزن المؤجل
                 */
                'postponed' => $shipment->update([
                    'current_location'   => Shipment::LOCATION_BRANCH,
                    'sub_status'         => Shipment::SUB_DEFERRED,
                    'current_courier_id' => null,
                ]),

                /**
                 * ✅ مرتجعة
                 * - تدخل مخزن المرتجعات
                 */
                'returned' => $shipment->update([
                    'current_location'   => Shipment::LOCATION_BRANCH,
                    'sub_status'         => Shipment::SUB_TRANSFERRED,
                    'current_courier_id' => null,
                ]),

                default => null,
            };
        }
    }
}
