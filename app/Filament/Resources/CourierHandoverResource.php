<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourierHandoverResource\Pages;
use App\Models\CourierHandover;
use App\Services\Finance\CourierHandoverFinanceService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CourierHandoverResource extends Resource
{
    protected static ?string $model = CourierHandover::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'العمليات المالية';
    protected static ?string $label = 'تصفية عهدة';
    protected static ?string $pluralLabel = 'تصفيات المناديب';
public static function shouldRegisterNavigation(): bool
{
    return false; // 👈 ده هيخفيه من السايد بار بس الصفحة هتفضل شغالة
}
    // نمنع الإنشاء اليدوي من القائمة الجانبية (يجب أن يتم من صفحة المندوب)
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('رقم التصفية')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('courier.name')->label('المندوب')->weight('bold'),
                Tables\Columns\TextColumn::make('branch.name')->label('الفرع'),

                Tables\Columns\TextColumn::make('cod_total')
                    ->label('إجمالي الـ COD')
                    ->money('EGP')
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('shipments_count')
                    ->label('عدد الشحنات')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'confirmed' => 'success',
                    }),

                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // === الهيدر: ملخص التصفية ===
                Infolists\Components\Section::make('تفاصيل التصفية')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)->schema([
                                Infolists\Components\TextEntry::make('id')->label('رقم العملية #'),
                                Infolists\Components\TextEntry::make('created_at')->label('التاريخ')->dateTime(),
                                Infolists\Components\TextEntry::make('courier.name')->label('المندوب')->icon('heroicon-m-truck'),
                                Infolists\Components\TextEntry::make('branch.name')->label('الفرع المستلم')->icon('heroicon-m-building-office'),
                            ]),
                            Infolists\Components\Grid::make(1)->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('حالة التصفية')
                                    ->badge()
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->color(fn (string $state): string => match ($state) {
                                        'open' => 'warning',
                                        'confirmed' => 'success',
                                    }),
                            ]),
                        ])->from('md'),
                    ]),

                // === الأرقام المالية ===
                Infolists\Components\Section::make('الموقف المالي (Financial Summary)')
                    ->schema([
                        Infolists\Components\Grid::make(3)->schema([
                            Infolists\Components\TextEntry::make('cod_total')
                                ->label('إجمالي النقدية (COD) للتوريد')
                                ->money('EGP')
                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                ->color('danger')
                                ->helperText('يجب استلام هذا المبلغ من المندوب'),

                            // هنا ممكن نحسب العمولة من الـ Items لو مش متخزنة في الهيدر، بس إحنا بنخزنها
                            // بما أن الجدول عندك مفيهوش total_commission، ممكن نعرض عدد الشحنات حالياً
                            Infolists\Components\TextEntry::make('shipments_count')
                                ->label('عدد الشحنات المشمولة')
                                ->badge(),
                        ]),
                    ]),

                // === جدول الشحنات داخل التصفية ===
                Infolists\Components\Section::make('قائمة الشحنات')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items') // علاقة items
                            ->label('')
                            ->schema([
                                Infolists\Components\Grid::make(4)->schema([
                                    Infolists\Components\TextEntry::make('shipment.tracking_number')->label('البوليصة'),
                                    Infolists\Components\TextEntry::make('item_type')
                                        ->label('نوع الحركة')
                                        ->badge()
                                        ->color(fn ($state) => $state === 'delivered' ? 'success' : 'warning'),
                                    Infolists\Components\TextEntry::make('cod_amount')->label('قيمة الـ COD')->money('EGP'),
                                    Infolists\Components\TextEntry::make('shipment.area.name')->label('المنطقة'),
                                ]),
                            ])
                            ->grid(1)
                            ->contained(false),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourierHandovers::route('/'),
            // 'create' => ... (ملغية)
            'view' => Pages\ViewCourierHandover::route('/{record}'),
        ];
    }
}
