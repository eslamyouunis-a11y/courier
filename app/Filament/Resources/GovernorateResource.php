<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GovernorateResource\Pages;
use App\Models\Governorate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GovernorateResource extends Resource
{
    protected static ?string $model = Governorate::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    // المجموعة في السايد بار
    protected static ?string $navigationGroup = ' التوزيع الجغرافي';

    // الترتيب داخل المجموعة
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'المحافظات';

    protected static ?string $pluralLabel = 'المحافظات';

    protected static ?string $modelLabel = 'محافظة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات المحافظة')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المحافظة')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('shipping_cost')
                            ->label('تكلفة الشحن الأساسية')
                            ->numeric()
                            ->prefix('ج.م')
                            ->required()
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('مفعله للنظام')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('المحافظة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('shipping_cost')
                    ->label('سعر الشحن')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGovernorates::route('/'),
        ];
    }
}
