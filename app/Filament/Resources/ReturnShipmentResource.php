<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnShipmentResource\Pages;
use App\Models\Shipment;
use App\Models\MerchantReturnMission;
use App\Models\MerchantReturnMissionItem;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontFamily;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturnShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationGroup = 'التشغيل';
    protected static ?string $navigationLabel = 'المرتجعات';
    protected static ?string $label = 'مرتجع';
    protected static ?string $pluralLabel = 'المرتجعات';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', Shipment::STATUS_RETURNED)
            ->where('current_location', Shipment::LOCATION_BRANCH)
            ->whereNull('merchant_return_mission_id'); // فقط التي لم تخرج في مهمة بعد
    }


    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('البوليصة')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->fontFamily(FontFamily::Mono),

                Tables\Columns\TextColumn::make('merchant.name')
                    ->label('التاجر')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('العميل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('return_reason')
                    ->label('سبب الارتجاع')
                    ->limit(30)
                    ->color('danger')
                    ->tooltip(fn (Shipment $record) => $record->return_reason),

                Tables\Columns\TextColumn::make('area.name')
                    ->label('المنطقة'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ الوصول للمخزن')
                    ->since()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('merchant_id')
                    ->label('التاجر')
                    ->relationship('merchant', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Shipment $record) => ShipmentResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('create_return_mission')
                        ->label('إنشاء إذن تسليم للتاجر')
                        ->icon('heroicon-m-arrow-right-on-rectangle')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $merchantId = $records->first()->merchant_id;

                            if ($records->pluck('merchant_id')->unique()->count() > 1) {
                                Notification::make()->title('خطأ: يجب اختيار شحنات لتاجر واحد فقط')->danger()->send();
                                return;
                            }

                            DB::transaction(function () use ($records, $merchantId) {
                                $mission = MerchantReturnMission::create([
                                    'merchant_id' => $merchantId,
                                    'status' => 'open',
                                    'shipments_count' => $records->count(),
                                    'created_by' => Auth::id(),
                                ]);

                                foreach ($records as $shipment) {
                                    MerchantReturnMissionItem::create([
                                        'mission_id' => $mission->id,
                                        'shipment_id' => $shipment->id,
                                    ]);
                                    $shipment->update(['merchant_return_mission_id' => $mission->id]);
                                }

                                Notification::make()->title('تم إنشاء إذن التسليم بنجاح')->success()->send();
                            });
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnShipments::route('/'),
        ];
    }
}
