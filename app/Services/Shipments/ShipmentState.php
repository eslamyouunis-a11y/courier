<?php

namespace App\Services\Shipments;

use App\Models\Shipment;
use LogicException;

final class ShipmentState
{
    /* =============================
     | Guards (Can?)
     |=============================*/

    public static function canAssignCourier(Shipment $shipment): bool
    {
        return $shipment->status === Shipment::STATUS_IN_PROGRESS
            && in_array($shipment->sub_status, [
                Shipment::SUB_IN_STOCK,
                Shipment::SUB_DEFERRED,
            ], true);
    }

    public static function canMoveWithCourier(Shipment $shipment): bool
    {
        return $shipment->status === Shipment::STATUS_IN_PROGRESS
            && $shipment->sub_status === Shipment::SUB_ASSIGNED;
    }

    public static function canDeliver(Shipment $shipment): bool
    {
        return $shipment->status === Shipment::STATUS_IN_PROGRESS
            && $shipment->sub_status === Shipment::SUB_WITH_COURIER;
    }

    public static function canReturnToSender(Shipment $shipment): bool
    {
        return self::canDeliver($shipment);
    }

    /* =============================
     | Assertions
     |=============================*/

    public static function assert(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new LogicException($message);
        }
    }
}
