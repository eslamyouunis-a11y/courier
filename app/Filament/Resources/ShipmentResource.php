<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShipmentResource\Pages;
use App\Models\Shipment;
use App\Models\Courier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'التشغيل';
    protected static ?string $label = 'شحنة';
    protected static ?string $pluralLabel = 'الشحنات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات التشغيل (Operation)')
                    ->schema([
                        Forms\Components\Select::make('merchant_id')
                            ->label('التاجر')
                            ->relationship('merchant', 'name')
                            ->searchable()
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('branch_id')
                            ->label('الفرع المسؤول')
                            ->relationship('branch', 'name')
                            ->default(fn () => Auth::user()?->branch_id)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('بيانات المستلم (Customer)')
                    ->icon('heroicon-m-user')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')->label('اسم العميل')->required(),
                        Forms\Components\TextInput::make('customer_phone')->label('رقم الهاتف')->tel()->required(),
                        Forms\Components\Select::make('governorate_id')
                            ->label('المحافظة')
                            ->relationship('governorate', 'name')
                            ->searchable()
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('area_id')
                            ->label('المنطقة / المدينة')
                            ->relationship('area', 'name', fn ($query, $get) =>
                                $query->where('governorate_id', $get('governorate_id'))
                            )
                            ->searchable()
                            ->required(),
                        Forms\Components\Textarea::make('customer_address')->label('العنوان بالتفصيل')->required()->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('البيانات المالية (Financials)')
                    ->icon('heroicon-m-currency-dollar')
                    ->schema([
                        Forms\Components\TextInput::make('amount')->label('قيمة الشحنة (COD)')->numeric()->prefix('EGP')->required(),
                        Forms\Components\TextInput::make('shipping_fees')->label('مصاريف الشحن')->numeric()->prefix('EGP'),
                        Forms\Components\DatePicker::make('expected_delivery_date')->label('موعد التسليم المتوقع')->default(now()->addDays(2)),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->columns([
                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('البوليصة')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('merchant.name')->label('التاجر')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Shipment::STATUS_SAVED => 'gray',
                        Shipment::STATUS_IN_PROGRESS => 'warning',
                        Shipment::STATUS_DELIVERED => 'success',
                        Shipment::STATUS_RETURNED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('sub_status')->label('الموقف الحالي')->badge()->color('info'),
                Tables\Columns\TextColumn::make('amount')->label('COD')->money('EGP')->weight('bold')->color('danger'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('فتح الكنترول'),
                Tables\Actions\EditAction::make()->label('تعديل'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                    // 🚚 1. قبول جماعي (يظهر في تاب المحفوظة)
                    Tables\Actions\BulkAction::make('bulk_accept')
                        ->label('قبول في الفرع')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'saved')
                        ->action(function (Collection $records) {
                            $records->each->update([
                                'status' => Shipment::STATUS_IN_PROGRESS,
                                'sub_status' => Shipment::SUB_IN_STOCK,
                                'current_location' => Shipment::LOCATION_BRANCH
                            ]);
                            Notification::make()->title('تم قبول الشحنات بنجاح')->success()->send();
                        }),

                    // 🛵 2. تعيين لمندوب (يظهر في تاب المخزن)
                    Tables\Actions\BulkAction::make('bulk_assign')
                        ->label('تعيين لمندوب')
                        ->icon('heroicon-m-user-plus')
                        ->color('info')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'in_stock')
                        ->form([
                            Forms\Components\Select::make('courier_id')
                                ->label('اختر المندوب')
                                ->options(fn () => Courier::where('branch_id', Auth::user()?->branch_id)->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update([
                                'sub_status' => Shipment::SUB_ASSIGNED,
                                'courier_id' => $data['courier_id']
                            ]);
                            Notification::make()->title('تم التعيين للمندوب')->success()->send();
                        }),

                    // ✅ 3. تم التسليم (يظهر في المخزن أو مع المندوب)
                    Tables\Actions\BulkAction::make('bulk_delivered')
                        ->label('تأكيد تسليم (Delivered)')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->visible(fn ($livewire) => in_array($livewire->activeTab, ['in_stock', 'with_courier', 'assigned']))
                        ->requiresConfirmation()
                        ->action(function (Collection $records, \App\Services\Finance\DeliveryFinanceService $service) {
                            foreach ($records as $record) {
                                // هنا بننادي السيرفس اللي بتودي الفلوس في عهدة المندوب فقط
                                $service->onDelivered($record, Auth::id());

                                $record->update([
                                    'status' => Shipment::STATUS_DELIVERED,
                                    'sub_status' => null,
                                    'delivered_at' => now()
                                ]);
                            }
                            Notification::make()->title('تم تحديث الشحنات لـ تم التسليم وفي عهدة المندوب')->success()->send();
                        }),

                    // 💰 4. توريد عهدة (Handover) - يظهر فقط في تاب "تم التسليم"
                    Tables\Actions\BulkAction::make('bulk_handover')
                        ->label('توريد عهدة (Handover)')
                        ->icon('heroicon-m-banknotes')
                        ->color('success')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'delivered')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            // TODO: سيتم ربطها بـ HandoverService لنقل الأموال من المندوب للفرع والتاجر
                            Notification::make()->title('جاري معالجة توريد النقدية...')->info()->send();
                        }),

                    // ⏰ 5. تأجيل (جماعي)
                    Tables\Actions\BulkAction::make('bulk_postpone')
                        ->label('تأجيل المختار')
                        ->icon('heroicon-m-clock')
                        ->color('warning')
                        ->visible(fn ($livewire) => in_array($livewire->activeTab, ['in_stock', 'with_courier']))
                        ->form([
                            Forms\Components\DatePicker::make('date')->label('تأجيل إلى تاريخ')->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update([
                                'sub_status' => Shipment::SUB_DEFERRED,
                                'expected_delivery_date' => $data['date']
                            ]);
                        }),

                    // ↩️ 6. المرتجعات الجماعية (تظهر في تاب المخزن أو مع المندوب)
                    Tables\Actions\BulkAction::make('bulk_ret_sender')
                        ->label('مرتجع على الراسل')
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('danger')
                        ->visible(fn ($livewire) => in_array($livewire->activeTab, ['in_stock', 'with_courier']))
                        ->action(fn (Collection $records) => $records->each->update(['status' => Shipment::STATUS_RETURNED, 'return_reason' => 'على الراسل'])),

                    Tables\Actions\BulkAction::make('bulk_ret_paid')
                        ->label('مرتجع مدفوع')
                        ->icon('heroicon-m-banknotes')
                        ->color('warning')
                        ->visible(fn ($livewire) => in_array($livewire->activeTab, ['in_stock', 'with_courier']))
                        ->action(fn (Collection $records) => $records->each->update(['status' => Shipment::STATUS_RETURNED, 'return_reason' => 'مدفوع'])),

                    Tables\Actions\DeleteBulkAction::make()->label('حذف المختار'),
                ])->label('الأوامر ')->icon('heroicon-m-bolt'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShipments::route('/'),
            'create' => Pages\CreateShipment::route('/create'),
            'view' => Pages\ViewShipment::route('/{record}'),
            'edit' => Pages\EditShipment::route('/{record}/edit'),
        ];
    }
}
