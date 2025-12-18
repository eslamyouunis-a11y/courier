<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Models\Area;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    // المجموعة في السايد بار (نفس مجموعة المحافظات)
    protected static ?string $navigationGroup = ' التوزيع الجغرافي';

    // الترتيب داخل المجموعة (بعد المحافظات)
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'المناطق الفرعية';
    protected static ?string $pluralLabel = 'المناطق الفرعية';
    protected static ?string $modelLabel = 'منطقة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('إضافة منطقة جديدة')
                    ->schema([
                        Forms\Components\Select::make('governorate_id')
                            ->label('المحافظة')
                            ->relationship('governorate', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المنطقة / الحي')
                            ->required(),
                        Forms\Components\TextInput::make('override_shipping_cost')
                            ->label('تعديل سعر الشحن (اختياري)')
                            ->numeric()
                            ->prefix('ج.م'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('المنطقة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('governorate.name')
                    ->label('المحافظة')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('override_shipping_cost')
                    ->label('سعر خاص')
                    ->money('EGP'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('governorate_id')
                    ->label('فلترة بالمحافظة')
                    ->relationship('governorate', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAreas::route('/'),
        ];
    }
}
