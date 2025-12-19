<?php

namespace App\Filament\Resources\MerchantResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class MerchantFinancialStats extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (!$this->record) return [];

        // أرقام افتراضية حالياً حتى يتم ربط المحفظة
        return [
            Stat::make('المستحقات الكلية', '0.00 ج.م')
                ->description('إجمالي الرصيد')
                ->icon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('قابل للسحب', '0.00 ج.م')
                ->description('جاهز للتحويل')
                ->icon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('عدد الشحنات', $this->record->shipments()->count())
                ->description('إجمالي الشحنات')
                ->icon('heroicon-m-cube')
                ->color('info'),
        ];
    }
}
