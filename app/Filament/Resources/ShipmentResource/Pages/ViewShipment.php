<?php

namespace App\Filament\Resources\ShipmentResource\Pages;

use App\Filament\Resources\ShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use App\Models\Branch;
use App\Models\User;
use Filament\Notifications\Notification;

class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $actions = [];

        // 1. الشحنة لسه جديدة (Saved) -> لازم نقبلها في فرع الأول
        if ($record->status === 'saved') {
            $actions[] = Action::make('accept')
                ->label('قبول في فرع')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('branch_id')
                        ->label('اختر الفرع المستلم')
                        ->options(Branch::pluck('name', 'id'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'in_stock',
                        'branch_id' => $data['branch_id'],
                        'accepted_at' => now(), // لو عندك العمود دة
                    ]);
                    Notification::make()->title('تم قبول الشحنة في المخزن')->success()->send();
                });

             $actions[] = Action::make('delete')
                ->label('حذف')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn() => $this->record->delete());
        }

        // 2. الشحنة في المخزن (In Stock) -> هنا نعين المندوب بتاع نفس الفرع
        elseif ($record->status === 'in_stock') {
            $actions[] = Action::make('assign_courier')
                ->label('تعيين لمندوب')
                ->icon('heroicon-o-user-plus')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('courier_id')
                        ->label('اختر المندوب')
                        // هنا الحل: بنجيب المستخدمين اللي في نفس فرع الشحنة دي بس
                        ->options(function() use ($record) {
                            return User::where('branch_id', $record->branch_id)->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'courier_id' => $data['courier_id'],
                        'status' => 'assigned',
                    ]);
                    Notification::make()->title('تم تعيين المندوب')->success()->send();
                });

            $actions[] = Action::make('defer_stock')
                ->label('تأجيل')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('date')->required()->label('تاريخ جديد'),
                    \Filament\Forms\Components\Select::make('reason')
                        ->options(['customer_request' => 'طلب عميل', 'out_of_zone' => 'خارج التغطية'])
                        ->required()->label('السبب'),
                ])
                ->action(function($data) {
                     $this->record->increment('defers_count');
                     $this->record->update(['expected_delivery_date'=>$data['date'], 'defer_reason'=>$data['reason']]);
                     Notification::make()->title('تم التأجيل')->success()->send();
                });
        }

        // 3. تم التعيين (Assigned) -> المندوب يستلم العهدة
        elseif ($record->status === 'assigned') {
            $actions[] = Action::make('handover')
                ->label('تسليم العهدة للمندوب')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->action(fn() => $this->record->update(['status' => 'with_courier']));

            $actions[] = Action::make('cancel_assign')
                ->label('إلغاء التعيين (رجوع للمخزن)')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->action(fn() => $this->record->update(['status' => 'in_stock', 'courier_id' => null]));
        }

        // 4. مع المندوب (With Courier) -> تسليم نهائي أو مرتجع أو تأجيل
        elseif (in_array($record->status, ['with_courier', 'deferred'])) {
            $actions[] = Action::make('delivered')
                ->label('تم التسليم بنجاح')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn() => $this->record->update(['status' => 'delivered']));

            $actions[] = ActionGroup::make([
                Action::make('ret_paid')
                    ->label('مرتجع مدفوع (شحن)')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('paid')->label('المبلغ المحصل')->numeric()->required(),
                        \Filament\Forms\Components\Select::make('reason')->options(['refused'=>'رفض الاستلام','wrong_item'=>'خطأ في الصنف'])->required(),
                    ])
                    ->action(fn($data) => $this->record->update(['status'=>'returned_paid', 'paid_amount'=>$data['paid'], 'return_reason'=>$data['reason']])),

                Action::make('ret_merchant')
                    ->label('مرتجع كامل للراسل')
                    ->form([\Filament\Forms\Components\Select::make('reason')->options(['cancelled'=>'إلغاء','damaged'=>'تالف'])->required()])
                    ->action(fn($data) => $this->record->update(['status'=>'returned_merchant', 'return_reason'=>$data['reason']])),
            ])->label('تسجيل مرتجع')->icon('heroicon-o-x-circle')->color('danger');

            $actions[] = Action::make('defer_courier')
                ->label('تأجيل')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('date')->required(),
                    \Filament\Forms\Components\Textarea::make('reason')->required(),
                ])
                ->action(function($data){
                    $this->record->increment('defers_count');
                    $this->record->update(['status'=>'deferred', 'expected_delivery_date'=>$data['date'], 'defer_reason'=>$data['reason']]);
                });
        }

        return [
            ActionGroup::make($actions)->label('الأوامر')->icon('heroicon-m-chevron-down')->button()->color('primary'),
            Actions\EditAction::make()->label('تعديل البيانات'),
        ];
    }
}
