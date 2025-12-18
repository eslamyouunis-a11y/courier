<?php

namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\AreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAreas extends ManageRecords
{
    protected static string $resource = AreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // تخصيص زرار الإضافة عشان يظهر بالعربي وبشكل شيك
            Actions\CreateAction::make()
                ->label('إضافة منطقة جديدة')
                ->modalHeading('إنشاء منطقة جديدة')
                ->modalDescription('تأكد من اختيار المحافظة الصحيحة لتحديد سعر الشحن التلقائي.')
                ->modalIcon('heroicon-o-map-pin')
                ->modalSubmitActionLabel('حفظ المنطقة'),
        ];
    }
}
