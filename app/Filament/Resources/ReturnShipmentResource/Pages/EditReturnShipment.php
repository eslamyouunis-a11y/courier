<?php

namespace App\Filament\Resources\ReturnShipmentResource\Pages;

use App\Filament\Resources\ReturnShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReturnShipment extends EditRecord
{
    protected static string $resource = ReturnShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
