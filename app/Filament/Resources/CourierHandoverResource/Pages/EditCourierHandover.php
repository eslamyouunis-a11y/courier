<?php

namespace App\Filament\Resources\CourierHandoverResource\Pages;

use App\Filament\Resources\CourierHandoverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourierHandover extends EditRecord
{
    protected static string $resource = CourierHandoverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
