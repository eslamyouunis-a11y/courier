<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    // الترتيب في السايد بار (بعد التجار مباشرة)
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'الفروع';
    protected static ?string $pluralLabel = 'الفروع';
    protected static ?string $modelLabel = 'فرع';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('البيانات الأساسية')
                            ->schema([
                                Forms\Components\TextInput::make('code')
                                    ->label('كود الفرع')
                                    ->disabled() // ممنوع التعديل يدوي
                                    ->placeholder('سيتم توليده تلقائياً'),
                                Forms\Components\TextInput::make('name')
                                    ->label('اسم الفرع')
                                    ->required(),
                                Forms\Components\Select::make('governorate_id')
                                    ->label('المحافظة')
                                    ->relationship('governorate', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])->columns(2),
                        Forms\Components\Tabs\Tab::make('بيانات التواصل والمدير')
                            ->schema([
                                Forms\Components\TextInput::make('manager_name')->label('اسم المدير'),
                                Forms\Components\TextInput::make('manager_phone')->label('تليفون المدير'),
                                Forms\Components\TextInput::make('phone')->label('تليفون الفرع'),
                                Forms\Components\TextInput::make('address')->label('العنوان بالتفصيل'),
                            ])->columns(2),
                    ])->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('الكود')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('الفرع')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('wallet_balance')
                    ->label('المحفظة')
                    ->money('EGP')
                    ->color('success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('governorate.name')->label('المحافظة')->badge(),
                Tables\Columns\IconColumn::make('is_active')->label('الحالة')->boolean(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('البروفايل')->color('primary')->icon('heroicon-s-user-circle'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Grid::make(3)
                    ->schema([
                        // كارت رصيد المحفظة
                        Components\Section::make('رصيد المحفظة')
                            ->schema([
                                Components\TextEntry::make('wallet_balance')
                                    ->label('')
                                    ->money('EGP')
                                    ->size(Components\TextEntry\TextEntrySize::Large)
                                    ->weight('black')
                                    ->color('success'),
                            ])->columnSpan(1),

                        // بيانات البروفايل
                        Components\Section::make('بروفايل الفرع')
                            ->schema([
                                Components\TextEntry::make('code')->label('كود الفرع'),
                                Components\TextEntry::make('name')->label('الاسم'),
                                Components\TextEntry::make('manager_name')->label('المدير'),
                                Components\TextEntry::make('manager_phone')->label('تواصل المدير'),
                                Components\TextEntry::make('phone')->label('تواصل الفرع'),
                                Components\TextEntry::make('address')->label('العنوان'),
                            ])->columns(2)->columnSpan(2),
                    ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBranches::route('/'),
        ];
    }
}
