<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourierResource\Pages;
use App\Filament\Resources\CourierResource\RelationManagers;
use App\Models\Courier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class CourierResource extends Resource
{
    protected static ?string $model = Courier::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $label = 'مندوب';
    protected static ?string $pluralLabel = 'المناديب';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // === بيانات المندوب ===
                Forms\Components\Section::make('البيانات الشخصية')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('كود المندوب')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('CR-XXXX'),

                        Forms\Components\Select::make('branch_id')
                            ->label('الفرع التابع')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('name')->label('الاسم')->required(),
                        Forms\Components\TextInput::make('phone')->label('الهاتف')->required()->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('id_number')->label('الرقم القومي')->numeric()->maxLength(14),

                        Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
                    ])->columns(2),

                // === العمولات ===
                Forms\Components\Section::make('هيكل العمولات (Commission Structure)')
                    ->schema([
                        Forms\Components\TextInput::make('commission_delivered')
                            ->label('عمولة التسليم')
                            ->numeric()->default(0)->suffix('EGP'),

                        Forms\Components\TextInput::make('commission_returned_sender')
                            ->label('عمولة مرتجع للراسل')
                            ->numeric()->default(0)->suffix('EGP'),

                        Forms\Components\TextInput::make('commission_returned_paid')
                            ->label('عمولة مرتجع (مدفوع)')
                            ->numeric()->default(0)->suffix('EGP'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->badge()->color('gray')->searchable(),
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('branch.name')->label('الفرع')->badge(),
                Tables\Columns\TextColumn::make('phone')->icon('heroicon-m-phone')->searchable(),

                // عرض رصيد المحفظة الفعلي
                Tables\Columns\TextColumn::make('wallet.balance')
                    ->label('العهدة الحالية')
                    ->money('EGP')
                    ->badge()
                    ->color(fn ($state) => $state < 0 ? 'success' : 'danger') // أحمر لو عليه فلوس
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')->boolean()->label('نشط'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    // تصميم صفحة العرض (البروفايل)
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(1)->schema([
                                Infolists\Components\TextEntry::make('name')->weight(FontWeight::Bold)->size(Infolists\Components\TextEntry\TextEntrySize::Large)->label('المندوب'),
                                Infolists\Components\TextEntry::make('branch.name')->label('الفرع')->badge()->color('info'),
                            ]),
                            Infolists\Components\Grid::make(3)->schema([
                                Infolists\Components\TextEntry::make('phone')->label('الهاتف'),
                                Infolists\Components\TextEntry::make('id_number')->label('الرقم القومي'),
                                Infolists\Components\TextEntry::make('is_active')->label('الحالة')->badge(),
                            ]),
                        ])->from('md'),
                    ]),

                // هنا هتظهر الـ Relation Managers تحت تلقائياً
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // 1. جدول الشحنات الحالية
            RelationManagers\ShipmentsRelationManager::class,

            // 2. جدول التصفيات (وهو المسؤول عن إظهار زرار التصفية)
            RelationManagers\HandoversRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCouriers::route('/'),
            'create' => Pages\CreateCourier::route('/create'),
            'view' => Pages\ViewCourier::route('/{record}'),
            'edit' => Pages\EditCourier::route('/{record}/edit'),
        ];
    }
}
