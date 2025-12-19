<?php

namespace App\Filament\Resources\ShipmentResource\Pages;

use App\Filament\Resources\ShipmentResource;
use App\Models\Shipment;
use App\Services\Finance\DeliveryFinanceService;
use App\Services\Finance\ReturnToSenderFinanceService;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // === القسم 1: الهيدر (الهوية) ===
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(1)->schema([
                                Infolists\Components\TextEntry::make('tracking_number')
                                    ->label('رقم البوليصة')
                                    ->weight(FontWeight::Black)
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->copyable()
                                    ->icon('heroicon-m-qr-code'),

                                Infolists\Components\TextEntry::make('merchant.name')
                                    ->label('التاجر')
                                    ->icon('heroicon-m-building-storefront'),
                            ]),

                            Infolists\Components\Grid::make(3)->schema([
                                Infolists\Components\TextEntry::make('status')->label('الحالة الرئيسية')->badge(),
                                Infolists\Components\TextEntry::make('sub_status')->label('الموقف التشغيلي')->badge()->color('gray'),
                                Infolists\Components\TextEntry::make('current_location')
                                    ->label('مكان الشحنة')
                                    ->formatStateUsing(fn (Shipment $record) =>
                                        $record->current_location === 'branch'
                                        ? "فرع: {$record->currentBranch?->name}"
                                        : "مع: {$record->currentCourier?->name}"
                                    )
                                    ->icon('heroicon-m-map-pin')
                                    ->color('warning'),
                            ]),
                        ])->from('md'),
                    ]),

                // === القسم 2 و 3: العميل والمالية ===
                Infolists\Components\Split::make([
                    Infolists\Components\Section::make('بيانات العميل')
                        ->icon('heroicon-m-user')
                        ->schema([
                            Infolists\Components\TextEntry::make('customer_name')->label('الاسم'),
                            Infolists\Components\TextEntry::make('customer_phone')
                                ->label('الهاتف')
                                ->url(fn ($state) => "tel:{$state}")
                                ->icon('heroicon-m-phone'),
                            Infolists\Components\TextEntry::make('customer_address')->label('العنوان')->icon('heroicon-m-map')->columnSpanFull(),
                            Infolists\Components\TextEntry::make('governorate.name')->label('المحافظة'),
                            Infolists\Components\TextEntry::make('area.name')->label('المنطقة'),
                        ])->columns(2),

                    Infolists\Components\Section::make('الموقف المالي')
                        ->icon('heroicon-m-currency-dollar')
                        ->schema([
                            Infolists\Components\TextEntry::make('amount')->label('مطلوب (COD)')->money('EGP')->size(Infolists\Components\TextEntry\TextEntrySize::Large)->weight('bold')->color('danger'),
                            Infolists\Components\TextEntry::make('shipping_fees')->label('مصاريف الشحن')->money('EGP'),
                            Infolists\Components\TextEntry::make('total_amount')->label('الإجمالي')->money('EGP')->color('gray'),
                        ])->columns(3),
                ])->from('lg'),

                // === القسم 4: الـ Timeline ===
                Infolists\Components\Section::make('سجل التواريخ والتشغيل')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')->label('تاريخ الإنشاء')->dateTime(),
                        Infolists\Components\TextEntry::make('executed_at')->label('خروج للمندوب')->dateTime()->placeholder('-'),
                        Infolists\Components\TextEntry::make('delivered_at')->label('تاريخ التسليم')->dateTime()->placeholder('-'),
                        Infolists\Components\TextEntry::make('expected_delivery_date')->label('التسليم المتوقع')->date()->color('success'),

                        Infolists\Components\TextEntry::make('last_deferred_at')->label('آخر تأجيل')->dateTime()->visible(fn (Shipment $record) => $record->defers_count > 0),
                        Infolists\Components\TextEntry::make('defer_reason')->label('سبب التأجيل')->visible(fn (Shipment $record) => $record->defers_count > 0)->color('danger'),
                    ])->columns(4)->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // 1. خروج للمندوب
            Actions\Action::make('dispatch_to_courier')
                ->label('تسليم للمندوب')
                ->icon('heroicon-m-truck')
                ->color('warning')
                ->visible(fn (Shipment $record) =>
                    $record->status === Shipment::STATUS_IN_PROGRESS &&
                    in_array($record->sub_status, [Shipment::SUB_IN_STOCK, Shipment::SUB_ASSIGNED, Shipment::SUB_DEFERRED])
                )
                ->form([
                    Forms\Components\Select::make('courier_id')
                        ->label('اختر المندوب')
                        ->relationship('courier', 'name')
                        ->required(),
                ])
                ->action(function (Shipment $record, array $data) {
                    $record->update([
                        'sub_status' => Shipment::SUB_WITH_COURIER,
                        'current_location' => Shipment::LOCATION_COURIER,
                        'current_courier_id' => $data['courier_id'],
                        'courier_id' => $data['courier_id'],
                        'executed_at' => now(),
                    ]);
                    Notification::make()->title('خرجت مع المندوب')->success()->send();
                }),

            // 2. تسليم (Delivered)
            Actions\Action::make('mark_delivered')
                ->label('تسليم (Delivered)')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Shipment $record) => $record->isWithCourier())
                ->action(function (Shipment $record, DeliveryFinanceService $service) {
                    try {
                        $service->onDelivered($record, Auth::id());
                        $record->update([
                            'status' => Shipment::STATUS_DELIVERED,
                            'sub_status' => null,
                            'delivered_at' => now(),
                            'current_courier_id' => null,
                        ]);
                        Notification::make()->title('تم التسليم بنجاح')->success()->send();
                    } catch (\Exception $e) {
                        Notification::make()->title('خطأ')->body($e->getMessage())->danger()->send();
                    }
                }),

            // 3. مرتجع (Returned)
            Actions\Action::make('mark_returned')
                ->label('مرتجع (Returned)')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->visible(fn (Shipment $record) => $record->isWithCourier())
                ->form([
                    Forms\Components\Select::make('return_type')
                        ->options(['sender' => 'على الراسل', 'paid' => 'مرتجع مدفوع'])
                        ->required(),
                    Forms\Components\Textarea::make('reason')->label('سبب الارتجاع')->required(),
                ])
                ->action(function (Shipment $record, array $data, ReturnToSenderFinanceService $service) {
                    if ($data['return_type'] === 'sender') {
                        $service->handle($record, Auth::id());
                    }
                    $record->update([
                        'status' => Shipment::STATUS_RETURNED,
                        'sub_status' => Shipment::SUB_TRANSFERRED,
                        'current_location' => Shipment::LOCATION_BRANCH,
                        'current_courier_id' => null,
                        'return_reason' => $data['reason'],
                    ]);
                    Notification::make()->title('تم تسجيل المرتجع')->success()->send();
                }),

            // 4. تأجيل (Postpone)
            Actions\Action::make('postpone')
                ->label('تأجيل (Postpone)')
                ->icon('heroicon-m-clock')
                ->color('gray')
                ->visible(fn (Shipment $record) => $record->isWithCourier())
                ->form([
                    Forms\Components\DatePicker::make('deferred_to_date')->label('تاريخ التأجيل')->minDate(now())->required(),
                    Forms\Components\TextInput::make('defer_reason')->label('سبب التأجيل')->required(),
                ])
                ->action(function (Shipment $record, array $data) {
                    $record->update([
                        'sub_status' => Shipment::SUB_DEFERRED,
                        'current_location' => Shipment::LOCATION_BRANCH,
                        'current_courier_id' => null,
                        'deferred_to_date' => $data['deferred_to_date'],
                        'defer_reason' => $data['defer_reason'],
                        'last_deferred_at' => now(),
                        'defers_count' => $record->defers_count + 1,
                    ]);
                    Notification::make()->title('تم تأجيل الشحنة')->success()->send();
                }),
        ];
    }
}
