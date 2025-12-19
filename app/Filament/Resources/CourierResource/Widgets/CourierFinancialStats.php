<?php

namespace App\Filament\Resources\CourierResource\Widgets;

use App\Services\Finance\FinanceTypes;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class CourierFinancialStats extends BaseWidget
{
    public ?Model $record = null; // المندوب الحالي

    protected function getStats(): array
    {
        // 1. حساب العهدة (COD Outstanding)
        // الفلوس اللي معاه = (استلم عهدة) - (سلم عهدة)
        $codInHand = $this->record->wallet->transactions()
            ->where('type', FinanceTypes::COURIER_COD_ACCRUAL)
            ->sum('amount')
            -
            $this->record->wallet->transactions()
            ->where('type', FinanceTypes::COURIER_COD_HANDOVER)
            ->sum('amount');

        // 2. حساب العمولات المستحقة (Accrued Commission)
        // عمولات ليه = (عمولات استحقها) - (عمولات قبضها)
        $commDue = $this->record->wallet->transactions()
            ->whereIn('type', [
                FinanceTypes::COURIER_COMMISSION_DELIVERED,
                FinanceTypes::COURIER_COMMISSION_RETURNED_SENDER,
                FinanceTypes::COURIER_COMMISSION_RETURNED_PAID
            ])->sum('amount')
            -
            $this->record->wallet->transactions()
            ->where('type', FinanceTypes::COURIER_COMMISSION_PAYOUT)
            ->sum('amount');

        return [
            Stat::make('📦 عهدة نقدية (COD)', number_format($codInHand, 2) . ' EGP')
                ->description('أموال يجب توريدها للفرع')
                ->color($codInHand > 0 ? 'danger' : 'success')
                ->icon('heroicon-m-banknotes'),

            Stat::make('💰 عمولات مستحقة', number_format($commDue, 2) . ' EGP')
                ->description('رصيد أرباح المندوب')
                ->color('info')
                ->icon('heroicon-m-currency-dollar'),
        ];
    }
}
