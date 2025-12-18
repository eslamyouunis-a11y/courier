<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MerchantResource\Pages;
use App\Models\Merchant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class MerchantResource extends Resource
{
    protected static ?string $model = Merchant::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    // الترتيب في السايد بار (بعد الرئيسية مباشرة)
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'التجار';
    protected static ?string $pluralLabel = 'التجار';
    protected static ?string $modelLabel = 'تاجر';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات التاجر')
                    ->schema([
                        Forms\Components\TextInput::make('code')->label('كود التاجر')->disabled()->placeholder('تلقائي'),
                        Forms\Components\TextInput::make('name')->label('اسم التاجر / المتجر')->required(),
                        Forms\Components\TextInput::make('phone')->label('رقم الهاتف')->tel()->required(),
                        Forms\Components\Select::make('branch_id')
                            ->label('الفرع التابع له')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('address')->label('العنوان بالتفصيل')->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('الكود')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('التاجر')->searchable()->weight('bold'),

                // عرض محفظة المستحقات باللون الأخضر
                Tables\Columns\TextColumn::make('cod_balance')
                    ->label('مستحقات (COD)')
                    ->money('EGP')
                    ->color('success'),

                // عرض رصيد الشحن باللون الأزرق
                Tables\Columns\TextColumn::make('prepaid_balance')
                    ->label('رصيد الشحن')
                    ->money('EGP')
                    ->color('info'),

                Tables\Columns\TextColumn::make('branch.name')->label('الفرع')->badge(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('البروفايل')->icon('heroicon-s-user-circle'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('الحسابات المالية للتاجر')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('cod_balance')
                                    ->label('إجمالي مستحقات الطرود (التي لم تصرف بعد)')
                                    ->money('EGP')
                                    ->color('success')
                                    ->size(Components\TextEntry\TextEntrySize::Large)
                                    ->weight('black'),
                                Components\TextEntry::make('prepaid_balance')
                                    ->label('رصيد محفظة الشحن المسبق')
                                    ->money('EGP')
                                    ->color('info')
                                    ->size(Components\TextEntry\TextEntrySize::Large)
                                    ->weight('black'),
                            ]),
                    ]),
                Components\Section::make('معلومات التاجر')
                    ->schema([
                        Components\TextEntry::make('code')->label('كود التاجر'),
                        Components\TextEntry::make('name')->label('اسم التاجر'),
                        Components\TextEntry::make('phone')->label('رقم الهاتف'),
                        Components\TextEntry::make('branch.name')->label('الفرع المربوط به'),
                        Components\TextEntry::make('address')->label('العنوان'),
                    ])->columns(2)
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMerchants::route('/'),
        ];
    }
}
