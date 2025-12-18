<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourierResource\Pages;
use App\Models\Courier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class CourierResource extends Resource
{
    protected static ?string $model = Courier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    // الترتيب في السايد بار (بعد الفروع مباشرة)
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'المناديب';
    protected static ?string $pluralLabel = 'المناديب';
    protected static ?string $modelLabel = 'مندوب';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات المندوب الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('كود المندوب')
                            ->disabled()
                            ->placeholder('سيتم توليده تلقائياً'),
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المندوب')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم التليفون')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('id_number')
                            ->label('رقم البطاقة الشخصية')
                            ->maxLength(14),
                        Forms\Components\Select::make('branch_id')
                            ->label('الفرع التابع له')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('الكود')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('المندوب')
                    ->searchable()
                    ->weight('bold'),

                // محفظة التحصيلات (فلوس الشركة)
                Tables\Columns\TextColumn::make('cod_wallet')
                    ->label('التحصيلات (COD)')
                    ->money('EGP')
                    ->color('danger')
                    ->description('مبالغ عهدة'),

                // محفظة العمولات (فلوس المندوب)
                Tables\Columns\TextColumn::make('commission_wallet')
                    ->label('العمولات')
                    ->money('EGP')
                    ->color('success')
                    ->description('مستحقات المندوب'),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('فلترة بالفرع')
                    ->relationship('branch', 'name'),
            ])
            ->actions([
                // زرار البروفايل المالي (View)
                Tables\Actions\ViewAction::make()
                    ->label('البروفايل المالي')
                    ->icon('heroicon-s-banknotes')
                    ->color('info'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('المركز المالي الحالي')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                // كارت التحصيلات
                                Components\TextEntry::make('cod_wallet')
                                    ->label('إجمالي مبالغ التحصيل (COD)')
                                    ->money('EGP')
                                    ->color('danger')
                                    ->size(Components\TextEntry\TextEntrySize::Large)
                                    ->weight('black'),

                                // كارت العمولات
                                Components\TextEntry::make('commission_wallet')
                                    ->label('إجمالي العمولات المستحقة')
                                    ->money('EGP')
                                    ->color('success')
                                    ->size(Components\TextEntry\TextEntrySize::Large)
                                    ->weight('black'),
                            ]),
                    ]),

                Components\Section::make('البيانات الشخصية')
                    ->schema([
                        Components\TextEntry::make('code')->label('الكود'),
                        Components\TextEntry::make('name')->label('الاسم'),
                        Components\TextEntry::make('phone')->label('رقم الهاتف'),
                        Components\TextEntry::make('id_number')->label('رقم البطاقة'),
                        Components\TextEntry::make('branch.name')->label('الفرع'),
                    ])->columns(3)
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCouriers::route('/'),
        ];
    }
}
