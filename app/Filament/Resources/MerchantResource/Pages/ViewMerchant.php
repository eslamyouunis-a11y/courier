<?php

namespace App\Filament\Resources\MerchantResource\Pages;

use App\Filament\Resources\MerchantResource;
use App\Filament\Resources\MerchantResource\Widgets\MerchantFinancialStats;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMerchant extends ViewRecord
{
    protected static string $resource = MerchantResource::class;

    // 👇 دالة الأزرار العلوية (تمت إضافتها)
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل التاجر')
                ->icon('heroicon-m-pencil-square'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MerchantFinancialStats::class,
        ];
    }
}
