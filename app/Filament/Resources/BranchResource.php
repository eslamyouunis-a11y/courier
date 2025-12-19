<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Filament\Resources\BranchResource\RelationManagers;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $label = 'الفرع';
    protected static ?string $pluralLabel = 'الفروع';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('البيانات الأساسية')
                    ->schema([
                        // الكود يظهر فقط ولكن لا يمكن تعديله لأنه Auto Generated
                        Forms\Components\TextInput::make('code')
                            ->label('كود الفرع')
                            ->disabled()
                            ->dehydrated(false) // لا يرسل للداتابيز
                            ->placeholder('تلقائي (BR-XXX)'),

                        Forms\Components\TextInput::make('name')
                            ->label('اسم الفرع')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('governorate_id')
                            ->label('المحافظة')
                            ->relationship('governorate', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('address')
                            ->label('العنوان')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('هاتف الفرع الأرضي')
                            ->tel(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('الفرع نشط')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),

                Forms\Components\Section::make('الإدارة والمسؤولين')
                    ->schema([
                        Forms\Components\TextInput::make('manager_name')
                            ->label('اسم المدير')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('manager_phone')
                            ->label('هاتف المدير')
                            ->tel()
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('كود')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('name')
                    ->label('الفرع')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('governorate.name')
                    ->label('المحافظة')
                    ->icon('heroicon-m-map-pin')
                    ->sortable(),

                Tables\Columns\TextColumn::make('manager_name')
                    ->label('المدير')
                    ->description(fn (Branch $record) => $record->manager_phone)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
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
                            // الجزء الأيمن: الهوية
                            Infolists\Components\Grid::make(1)->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('الفرع')
                                    ->weight(FontWeight::Bold)
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('code')
                                    ->label('الكود التعريفي')
                                    ->badge()
                                    ->color('gray'),

                                Infolists\Components\TextEntry::make('governorate.name')
                                    ->label('النطاق الجغرافي')
                                    ->icon('heroicon-m-map'),
                            ]),

                            // الجزء الأيسر: التواصل والإدارة
                            Infolists\Components\Grid::make(2)->schema([
                                Infolists\Components\TextEntry::make('manager_name')
                                    ->label('المدير المسؤول')
                                    ->icon('heroicon-m-user-circle'),

                                Infolists\Components\TextEntry::make('manager_phone')
                                    ->label('تواصل المدير')
                                    ->icon('heroicon-m-phone')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('phone')
                                    ->label('هاتف الفرع')
                                    ->icon('heroicon-m-phone'),

                                Infolists\Components\TextEntry::make('is_active')
                                    ->label('الحالة')
                                    ->badge()
                                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                            ]),
                        ])->from('md'),
                    ]),

                // التابات التشغيلية
                Infolists\Components\Tabs::make('Operations')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('حركة الشحنات')
                            ->icon('heroicon-m-cube')
                            ->schema([
                                Infolists\Components\TextEntry::make('')->hidden(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ShipmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'view' => Pages\ViewBranch::route('/{record}'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
