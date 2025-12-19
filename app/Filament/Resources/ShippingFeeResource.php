<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingFeeResource\Pages;
use App\Filament\Resources\ShippingFeeResource\RelationManagers;
use App\Models\ShippingFee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;

class ShippingFeeResource extends Resource
{
    protected static ?string $model = ShippingFee::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $label = 'مصاريف الشحن ';
    protected static ?string $pluralLabel = 'مصاريف الشحن ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('المسار الجغرافي')
                    ->schema([
                        Forms\Components\Select::make('from_governorate_id')
                            ->label('من محافظة')
                            ->relationship('fromGovernorate', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('to_governorate_id')
                            ->label('إلى محافظة')
                            ->relationship('toGovernorate', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('الهيكل المالي للرسوم')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('delivery_fee')->label('رسوم التوصيل')->numeric()->step(0.01)->required(),
                                Forms\Components\Select::make('delivery_fee_type')->options(['fixed' => 'ثابت', 'percent' => 'نسبة'])->default('fixed')->required(),
                                Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('return_fee')->label('المرتجع')->numeric()->step(0.01)->required(),
                                Forms\Components\Select::make('return_fee_type')->options(['fixed' => 'ثابت', 'percent' => 'نسبة'])->default('fixed')->required(),
                                Forms\Components\TextInput::make('cancel_fee')->label('الإلغاء')->numeric()->step(0.01)->required(),
                                Forms\Components\Select::make('cancel_fee_type')->options(['fixed' => 'ثابت', 'percent' => 'نسبة'])->default('fixed')->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    // 1. العنوان والحالة
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('route_name')
                            ->state(fn (ShippingFee $record) => "{$record->fromGovernorate->name} ⬅ {$record->toGovernorate->name}")
                            ->weight(FontWeight::Bold)
                            ->icon('heroicon-m-map-pin')
                            ->color('primary'),

                        Tables\Columns\IconColumn::make('is_active')
                            ->boolean()
                            ->grow(false),
                    ]),

                    // 2. سعر التوصيل الرئيسي
                    Tables\Columns\TextColumn::make('delivery_fee')
                        ->formatStateUsing(fn ($state) => number_format((float) $state, 0) . ' ج.م')
                        ->weight(FontWeight::Black)
                        ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                        ->color('success'),

                    // 3. تفاصيل الرسوم (المرتجع والإلغاء) - المطلوب إظهارها
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('return_fee')
                            ->label('المرتجع')
                            ->prefix('مرتجع: ') // نص توضيحي
                            ->formatStateUsing(fn ($state, $record) => $record->return_fee_type === 'percent' ? "%{$state}" : "{$state}")
                            ->color('gray')
                            ->size(Tables\Columns\TextColumn\TextColumnSize::Small),

                        Tables\Columns\TextColumn::make('cancel_fee')
                            ->label('الإلغاء')
                            ->prefix('إلغاء: ') // نص توضيحي
                            ->formatStateUsing(fn ($state, $record) => $record->cancel_fee_type === 'percent' ? "%{$state}" : "{$state}")
                            ->color('danger')
                            ->alignment('end') // محاذاة لليسار
                            ->size(Tables\Columns\TextColumn\TextColumnSize::Small),
                    ])->extraAttributes(['class' => 'border-b border-gray-100 pb-2 mb-2']), // خط فاصل خفيف

                    // 4. عرض المناطق المخصصة (لو موجودة)
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('area_label')
                            ->default('مناطق مخصصة:')
                            ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                            ->color('gray'),

                        Tables\Columns\TextColumn::make('areaShippingFees.area.name')
                            ->badge()
                            ->color('info')
                            ->separator(',') // يفصل بينهم بفاصلة لو كتيير
                            ->limitList(3)   // يعرض أول 3 والباقي +X
                    ])->visible(fn (?ShippingFee $record) => $record && $record->areaShippingFees()->exists()),

                ])
                ->space(3)
                ->extraAttributes([
                    'class' => 'bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-all'
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('from_governorate_id')->relationship('fromGovernorate', 'name')->label('من'),
                Tables\Filters\SelectFilter::make('to_governorate_id')->relationship('toGovernorate', 'name')->label('إلى'),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-horizontal')
                ->tooltip('خيارات'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AreaShippingFeesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingFees::route('/'),
            'create' => Pages\CreateShippingFee::route('/create'),
            'edit' => Pages\EditShippingFee::route('/{record}/edit'),
        ];
    }
}
