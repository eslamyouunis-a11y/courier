<?php

namespace App\Filament\Resources\CourierResource\Pages;

use App\Filament\Resources\CourierResource;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\CourierResource\Widgets\CourierFinancialStats;

class ViewCourier extends ViewRecord
{
    protected static string $resource = CourierResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            // ده الويدجت اللي هيظهر فوق البروفايل
            CourierFinancialStats::class,
        ];
    }
}
