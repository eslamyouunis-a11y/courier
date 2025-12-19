<?php

namespace App\Filament\Resources\ReturnShipmentResource\Pages;

use App\Filament\Resources\ReturnShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturnShipments extends ListRecords
{
    protected static string $resource = ReturnShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
