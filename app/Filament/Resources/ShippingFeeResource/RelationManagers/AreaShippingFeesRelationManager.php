<?php

namespace App\Filament\Resources\ShippingFeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Area;
use Illuminate\Database\Eloquent\Builder;

class AreaShippingFeesRelationManager extends RelationManager
{
    protected static string $relationship = 'areaShippingFees';

    protected static ?string $title = 'تخصيص أسعار المناطق';

    protected static ?string $label = 'تسعيرة منطقة';

    protected static ?string $pluralLabel = 'تسعيرات المناطق المخصصة';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تفاصيل المنطقة والسعر')
                    ->schema([
                        // اختيار المنطقة (مع فلترة ذكية)
                        Forms\Components\Select::make('area_id')
                            ->label('المنطقة')
                            ->relationship(
                                'area',
                                'name',
                                // فلتر: هات بس المناطق التابعة لمحافظة "الوصول" في المسار ده
                                fn (Builder $query, RelationManager $livewire) =>
                                    $query->where('governorate_id', $livewire->getOwnerRecord()->to_governorate_id)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        // هيكل الرسوم (مطابق للجدول)
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('delivery_fee')
                                    ->label('رسوم التوصيل')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required(),

                                Forms\Components\Select::make('delivery_fee_type')
                                    ->label('النوع')
                                    ->options(['fixed' => 'ثابت', 'percent' => 'نسبة'])
                                    ->default('fixed')
                                    ->required(),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('نشط')
                                    ->default(true)
                                    ->inline(false),
                            ]),

                        // رسوم إضافية
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('return_fee')
                                    ->label('رسوم المرتجع')
                                    ->numeric()->step(0.01)->default(0),
                                Forms\Components\Select::make('return_fee_type')
                                    ->label('نوع المرتجع')
                                    ->options(['fixed' => 'ثابت', 'percent' => 'نسبة'])
                                    ->default('fixed'),

                                Forms\Components\TextInput::make('cancel_fee')
                                    ->label('رسوم الإلغاء')
                                    ->numeric()->step(0.01)->default(0),
                                Forms\Components\Select::make('cancel_fee_type')
                                    ->label('نوع الإلغاء')
                                    ->options(['fixed' => 'ثابت', 'percent' => 'نسبة'])
                                    ->default('fixed'),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('area.name')
                    ->label('المنطقة')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('delivery_fee')
                    ->label('سعر التوصيل')
                    ->money('EGP')
                    ->description(fn ($record) => $record->delivery_fee_type === 'percent' ? 'نسبة مئوية' : 'مبلغ ثابت'),

                Tables\Columns\TextColumn::make('return_fee')
                    ->label('المرتجع')
                    ->money('EGP'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة تخصيص لمنطقة'),
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
}
