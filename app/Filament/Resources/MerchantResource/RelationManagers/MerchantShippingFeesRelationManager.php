<?php

namespace App\Filament\Resources\MerchantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MerchantShippingFeesRelationManager extends RelationManager
{
    protected static string $relationship = 'merchantShippingFees';

    protected static ?string $title = 'أسعار شحن مخصصة (Overrides)';
    protected static ?string $icon = 'heroicon-m-currency-dollar';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // القسم الأول: النطاق الجغرافي
                Forms\Components\Section::make('المسار الجغرافي')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('from_governorate_id')
                                ->label('من محافظة')
                                ->relationship('fromGovernorate', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),

                            Forms\Components\Select::make('to_governorate_id')
                                ->label('إلى محافظة')
                                ->relationship('toGovernorate', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->reactive() // عشان لما نغير المحافظة، المنطقة تتصفر
                                ->afterStateUpdated(fn (callable $set) => $set('area_id', null)),
                        ]),

                        Forms\Components\Select::make('area_id')
                            ->label('تخصيص لمنطقة محددة (اختياري)')
                            ->helperText('اتركه فارغاً لتطبيق السعر على كامل المحافظة')
                            ->relationship('area', 'name', fn ($query, $get) =>
                                // فلتر المناطق التابعة لمحافظة الوصول فقط
                                $query->where('governorate_id', $get('to_governorate_id'))
                            )
                            ->placeholder('عام (كل المحافظة)')
                            ->searchable()
                            ->preload(),
                    ]),

                // القسم الثاني: تفاصيل الأسعار
                Forms\Components\Section::make('تفاصيل الرسوم')
                    ->schema([
                        // 1. التوصيل
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('delivery_fee')
                                ->label('سعر التوصيل')
                                ->numeric()
                                ->required()
                                ->prefix('ج.م'),

                            Forms\Components\Select::make('delivery_fee_type')
                                ->label('نوع السعر')
                                ->options([
                                    'fixed' => 'مبلغ ثابت',
                                    'percent' => 'نسبة مئوية',
                                ])
                                ->default('fixed')
                                ->required(),
                        ]),

                        // 2. المرتجع
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('return_fee')
                                ->label('سعر المرتجع')
                                ->numeric()
                                ->default(0),

                            Forms\Components\Select::make('return_fee_type')
                                ->label('نوع المرتجع')
                                ->options([
                                    'fixed' => 'مبلغ ثابت',
                                    'percent' => 'نسبة مئوية',
                                ])
                                ->default('fixed'),
                        ]),

                        // 3. الإلغاء
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('cancel_fee')
                                ->label('سعر الإلغاء')
                                ->numeric()
                                ->default(0),

                            Forms\Components\Select::make('cancel_fee_type')
                                ->label('نوع الإلغاء')
                                ->options([
                                    'fixed' => 'مبلغ ثابت',
                                    'percent' => 'نسبة مئوية',
                                ])
                                ->default('fixed'),
                        ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('تفعيل هذا السعر')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('أسعار الشحن المخصصة') // عنوان واضح للجدول
            ->columns([
                Tables\Columns\TextColumn::make('fromGovernorate.name')
                    ->label('من')
                    ->sortable(),

                Tables\Columns\TextColumn::make('toGovernorate.name')
                    ->label('إلى')
                    ->sortable(),

                Tables\Columns\TextColumn::make('area.name')
                    ->label('المنطقة')
                    ->placeholder('كل المحافظة')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('delivery_fee')
                    ->label('التوصيل')
                    ->money('EGP')
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            // 👇 الزرار الأساسي في الهيدر
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة سعر جديد')
                    ->modalHeading('إضافة تسعير جديد للتاجر'),
            ])
            // 👇 الزرار الاحتياطي للظهور عند عدم وجود بيانات (هام جداً)
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة أول سعر مخصص')
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
