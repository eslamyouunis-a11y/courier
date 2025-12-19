<?php

namespace App\Filament\Resources\CourierHandoverResource\Pages;

use App\Filament\Resources\CourierHandoverResource;
use App\Models\CourierHandover;
use App\Services\Finance\CourierHandoverFinanceService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ViewCourierHandover extends ViewRecord
{
    protected static string $resource = CourierHandoverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('confirm_handover')
                ->label('تأكيد استلام العهدة (Confirm)')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('تأكيد استلام النقدية وتصفية العهدة')
                ->modalDescription('هل أنت متأكد من استلام المبلغ من المندوب؟ سيتم ترحيل القيود المالية ولا يمكن التراجع عن هذه الخطوة.')
                ->visible(fn (CourierHandover $record) => $record->status === 'open') // يظهر فقط لو مفتوحة
                ->action(function (CourierHandover $record, CourierHandoverFinanceService $service) {
                    try {
                        // 🔥 استدعاء السرفيس المالية
                        $service->confirm($record, Auth::id());

                        Notification::make()
                            ->title('تم تأكيد التصفية بنجاح')
                            ->success()
                            ->send();

                        $this->refreshFormData(['status']); // تحديث الصفحة

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('خطأ في العملية')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
