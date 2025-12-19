<?php

namespace App\Filament\Resources\CourierHandoverResource\Pages;

use App\Filament\Resources\CourierHandoverResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourierHandovers extends ListRecords
{
    protected static string $resource = CourierHandoverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
