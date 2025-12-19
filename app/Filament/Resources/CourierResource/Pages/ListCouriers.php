<?php

namespace App\Filament\Resources\CourierResource\Pages;

use App\Filament\Resources\CourierResource;
use App\Filament\Resources\CourierHandoverResource; // 👈 استدعاء ريسورس التصفيات
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCouriers extends ListRecords
{
    protected static string $resource = CourierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 🔴 زر الذهاب لتصفيات المناديب
            Actions\Action::make('go_to_handovers')
                ->label('أرشيف التصفيات')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('danger') // لون أحمر
                ->url(CourierHandoverResource::getUrl('index')), // رابط صفحة التصفيات

            // زر الإضافة العادي
            Actions\CreateAction::make(),
        ];
    }
}
