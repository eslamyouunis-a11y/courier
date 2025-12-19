<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MerchantResource\Pages;
use App\Filament\Resources\MerchantResource\RelationManagers;
use App\Models\Merchant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class MerchantResource extends Resource
{
    protected static ?string $model = Merchant::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $label = 'التاجر';
    protected static ?string $pluralLabel = 'التجار';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // === القسم الأول: البيانات الأساسية ===
                Forms\Components\Section::make('البيانات الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('كود التاجر')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('سيتم إنشاؤه تلقائياً'),

                        Forms\Components\TextInput::make('name')
                            ->label('اسم المتجر / التاجر')
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email(),

                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->required(),

                        Forms\Components\Select::make('branch_id')
                            ->label('الفرع التابع له')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                // === القسم الثاني: العنوان ===
                Forms\Components\Section::make('العنوان والنطاق')
                    ->schema([
                        Forms\Components\Select::make('governorate_id')
                            ->label('المحافظة')
                            ->relationship('governorate', 'name')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('area_id', null)),

                        Forms\Components\Select::make('area_id')
                            ->label('المنطقة')
                            ->relationship('area', 'name', fn ($query, $get) =>
                                $query->where('governorate_id', $get('governorate_id')))
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('address')
                            ->label('العنوان التفصيلي')
                            ->columnSpanFull(),
                    ])->columns(2),

                // === القسم الثالث: تخصيص أسعار الشحن (Repeater) ===
                Forms\Components\Section::make('تخصيص أسعار الشحن (Overrides)')
                    ->description('أضف أسعاراً خاصة لمسارات محددة.')
                    ->schema([
                        Forms\Components\Repeater::make('merchantShippingFees')
                            ->relationship()
                            ->label('قائمة الأسعار المخصصة')
                            ->addActionLabel('إضافة مسار جديد')
                            ->schema([
                                // السطر الأول: من وإلى
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('from_governorate_id')
                                        ->label('من محافظة')
                                        ->relationship('fromGovernorate', 'name')
                                        ->required()
                                        // الكود ده بيسحب قيمة المحافظة من الفورم الرئيسي
                                        ->default(fn (Forms\Get $get) => $get('../../governorate_id'))
                                        ->searchable(),

                                    Forms\Components\Select::make('to_governorate_id')
                                        ->label('إلى محافظة')
                                        ->relationship('toGovernorate', 'name')
                                        ->required()
                                        ->searchable()
                                        ->reactive()
                                        ->afterStateUpdated(fn (callable $set) => $set('area_id', null)),
                                ]),

                                // السطر الثاني: المنطقة
                                Forms\Components\Select::make('area_id')
                                    ->label('تخصيص لمنطقة معينة (اختياري)')
                                    ->relationship('area', 'name', fn ($query, $get) =>
                                        $query->where('governorate_id', $get('to_governorate_id'))
                                    )
                                    ->placeholder('اتركه فارغاً لتطبيق السعر على كل المحافظة')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpanFull(),

                                // السطر الثالث: تفاصيل الأسعار
                                Forms\Components\Group::make()->schema([
                                    Forms\Components\Grid::make(3)->schema([
                                        // 1. التوصيل
                                        Forms\Components\Group::make()->schema([
                                            Forms\Components\TextInput::make('delivery_fee')
                                                ->label('سعر التوصيل')
                                                ->numeric()->required()->prefix('ج.م'),
                                            Forms\Components\Select::make('delivery_fee_type')
                                                ->options(['fixed' => 'ثابت', 'percent' => '%'])
                                                ->default('fixed')->required()->label('النوع'),
                                        ]),

                                        // 2. المرتجع
                                        Forms\Components\Group::make()->schema([
                                            Forms\Components\TextInput::make('return_fee')
                                                ->label('سعر المرتجع')
                                                ->numeric()->default(0),
                                            Forms\Components\Select::make('return_fee_type')
                                                ->options(['fixed' => 'ثابت', 'percent' => '%'])
                                                ->default('fixed')->label('النوع'),
                                        ]),

                                        // 3. الإلغاء
                                        Forms\Components\Group::make()->schema([
                                            Forms\Components\TextInput::make('cancel_fee')
                                                ->label('سعر الإلغاء')
                                                ->numeric()->default(0),
                                            Forms\Components\Select::make('cancel_fee_type')
                                                ->options(['fixed' => 'ثابت', 'percent' => '%'])
                                                ->default('fixed')->label('النوع'),
                                        ]),
                                    ]),
                                ]),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('تفعيل هذا السعر')
                                    ->default(true),
                            ])
                            ->itemLabel(fn (array $state): ?string =>
                                isset($state['to_governorate_id']) ? 'سعر مخصص (اضغط للتعديل)' : null
                            )
                            ->collapsed(false)
                            ->cloneable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),

                // === القسم الرابع: الإعدادات العامة ===
                Forms\Components\Section::make('الإعدادات العامة')
                    ->schema([
                        Forms\Components\TextInput::make('return_shipping_percentage')
                            ->label('نسبة تحمل المرتجع (%)')
                            ->numeric()
                            ->default(100)
                            ->suffix('%'),

                        Forms\Components\TextInput::make('settlement_days')
                            ->label('أيام التسوية')
                            ->numeric()
                            ->default(1),

                        Forms\Components\Toggle::make('is_active')
                            ->label('حساب التاجر نشط')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->badge()->color('gray')->searchable(),
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('branch.name')->label('الفرع')->icon('heroicon-m-building-office'),
                Tables\Columns\TextColumn::make('phone')->icon('heroicon-m-phone'),
                Tables\Columns\TextColumn::make('shipments_count')->counts('shipments')->label('شحنات')->badge()->color('info'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('نشط'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(1)->schema([
                                Infolists\Components\TextEntry::make('name')->weight(FontWeight::Bold)->size(Infolists\Components\TextEntry\TextEntrySize::Large)->label('التاجر'),
                                Infolists\Components\TextEntry::make('branch.name')->label('الفرع التابع')->icon('heroicon-m-building-storefront'),
                            ]),
                            Infolists\Components\Grid::make(3)->schema([
                                Infolists\Components\TextEntry::make('phone')->label('الهاتف'),
                                Infolists\Components\TextEntry::make('settlement_days')->label('التسوية')->suffix(' أيام'),
                                Infolists\Components\TextEntry::make('is_active')->label('الحالة')->badge()->color(fn ($state) => $state ? 'success' : 'danger'),
                            ]),
                        ])->from('md'),
                    ]),

                Infolists\Components\Tabs::make('Operations')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('سياسات الأسعار (Overrides)')
                            ->icon('heroicon-m-currency-dollar')
                            ->badge(fn ($record) => $record->merchantShippingFees()->count())
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('merchantShippingFees')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\Grid::make(4)->schema([
                                            Infolists\Components\TextEntry::make('fromGovernorate.name')->label('من'),
                                            Infolists\Components\TextEntry::make('toGovernorate.name')->label('إلى'),
                                            Infolists\Components\TextEntry::make('area.name')->label('المنطقة')->placeholder('عام'),
                                            Infolists\Components\TextEntry::make('delivery_fee')->label('الشحن')->color('success')->weight('bold')->suffix(' ج.م'),
                                        ]),
                                    ])
                                    ->hidden(fn ($record) => $record->merchantShippingFees()->count() === 0),

                                Infolists\Components\TextEntry::make('empty')
                                    ->label('')
                                    ->default('لا توجد أسعار خاصة.')
                                    ->visible(fn ($record) => $record->merchantShippingFees()->count() === 0),
                            ]),

                        Infolists\Components\Tabs\Tab::make('سجل الشحنات')
                            ->icon('heroicon-m-cube')
                            ->schema([
                                // الشحنات بتظهر تحت الصفحة تلقائياً
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // الإبقاء على الشحنات هنا لتظهر في صفحة العرض
            RelationManagers\ShipmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMerchants::route('/'),
            'create' => Pages\CreateMerchant::route('/create'),
            'view' => Pages\ViewMerchant::route('/{record}'),
            'edit' => Pages\EditMerchant::route('/{record}/edit'),
        ];
    }
}
