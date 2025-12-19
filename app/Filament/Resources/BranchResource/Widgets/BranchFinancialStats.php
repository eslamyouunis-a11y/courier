<?php

namespace App\Filament\Resources\BranchResource\Widgets;

use App\Models\Shipment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class BranchFinancialStats extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (! $this->record) return [];

        // 1. حساب رصيد الخزنة (من علاقة wallet الـ Polymorphic)
        $safeBalance = $this->record->wallet ? $this->record->wallet->balance : 0;

        // 2. حساب العهدة الخارجية (شحنات مع مناديب)
        $cashWithCouriers = Shipment::where('branch_id', $this->record->id)
            ->where('status', 'out_for_delivery')
            ->sum('total_amount'); // تأكد ان العمود ده هو المبلغ المطلوب تحصيله

        // 3. إجمالي المسؤولية
        $totalLiability = $safeBalance + $cashWithCouriers;

        return [
            Stat::make('إجمالي المسؤولية', number_format($totalLiability) . ' ج.م')
                ->description('خزنة + عهدة خارجية')
                ->icon('heroicon-m-scale')
                ->color('primary'),

            Stat::make('رصيد الخزنة', number_format($safeBalance) . ' ج.م')
                ->description('رصيد المحفظة الحالي')
                ->icon('heroicon-m-wallet')
                ->color('success'),

            Stat::make('عهدة مع مناديب', number_format($cashWithCouriers) . ' ج.م')
                ->description('تحت التحصيل')
                ->icon('heroicon-m-truck')
                ->color('warning'),
        ];
    }
}
