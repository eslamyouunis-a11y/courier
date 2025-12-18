<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShipmentResource\Pages;
use App\Models\Shipment;
use App\Models\Area;
use App\Models\User;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Picqer\Barcode\BarcodeGeneratorSVG;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'الشحنات';
    protected static ?string $pluralModelLabel = 'الشحنات';
    protected static ?string $modelLabel = 'شحنة';

    public static function getTranslatedStatus(string $status): string
    {
        return match ($status) {
            'saved'             => 'محفوظة (جديدة)',
            'in_stock'          => 'في المخزن (جاهزة للتوزيع)',
            'assigned'          => 'تم إسنادها للمندوب',
            'with_courier'      => 'خرجت للتسليم',
            'delivered'         => 'تم التسليم',
            'returned_paid'     => 'مرتجع (تحصيل شحن)',
            'returned_merchant' => 'مرتجع للراسل',
            'cancelled'         => 'ملغاة',
            'deferred'          => 'مؤجلة',
            default             => $status,
        };
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // الهيدر (شكل روبوست)
                Section::make()
                    ->schema([
                        Grid::make(3)->schema([
                            Group::make([
                                TextEntry::make('barcode_visual')->label('')->getStateUsing(fn ($record) => $record->tracking_number)
                                    ->formatStateUsing(function ($state) {
                                        $generator = new BarcodeGeneratorSVG();
                                        $barcodeSVG = $generator->getBarcode($state, $generator::TYPE_CODE_128, 2, 35);
                                        return new HtmlString("<div class='flex flex-col items-center'><div class='bg-white p-2 rounded border'>{$barcodeSVG}</div><span class='text-xs font-bold mt-1'>{$state}</span></div>");
                                    })->alignCenter(),
                            ]),
                            Group::make([
                                TextEntry::make('tracking_number_label')->default('رقم التتبع')->label('')->weight(FontWeight::Bold)->alignCenter()->color('gray'),
                                TextEntry::make('tracking_number')->label('')->copyable()
                                    ->extraAttributes(['class' => 'bg-amber-50 text-amber-900 border border-amber-200 px-4 py-2 rounded-lg font-mono text-xl font-bold shadow-sm text-center block w-fit mx-auto'])->alignCenter(),
                            ]),
                            Group::make([
                                TextEntry::make('status_label')->default('الحالة الحالية')->label('')->weight(FontWeight::Bold)->alignCenter()->color('gray'),
                                TextEntry::make('status')->label('')->badge()
                                    ->formatStateUsing(fn ($state) => self::getTranslatedStatus($state))
                                    ->color(fn ($state) => match($state){'delivered'=>'success','cancelled','returned_merchant'=>'danger','saved'=>'gray',default=>'info'})
                                    ->size(TextEntry\TextEntrySize::Large)->alignCenter(),
                            ]),
                        ])
                    ])->compact()->extraAttributes(['class' => 'max-w-5xl mx-auto bg-white border border-gray-200 rounded-2xl p-4 mb-6 shadow-sm']),

                // تفاصيل (تم حل مشكلة italic هنا بحذفها)
                Grid::make(3)->schema([
                    Section::make('العميل')->columnSpan(1)->schema([
                        TextEntry::make('customer_name')->label('الاسم')->weight(FontWeight::Bold),
                        TextEntry::make('customer_phone')->label('الموبايل')->icon('heroicon-m-phone')->copyable(),
                        TextEntry::make('governorate.name')->label('المحافظة')->badge(),
                        TextEntry::make('area.name')->label('المنطقة')->badge()->color('success'),
                        TextEntry::make('customer_address')->label('العنوان')->columnSpanFull(),
                    ])->compact(),

                    Section::make('التنفيذ')->columnSpan(1)->schema([
                        TextEntry::make('branch.name')->label('الفرع')->placeholder('لم يحدد'),
                        TextEntry::make('courier.name')->label('المندوب')->placeholder('لم يعين')->color('warning'),
                    ])->compact(),

                    Section::make('الماليات')->columnSpan(1)->schema([
                        TextEntry::make('amount')->label('السعر')->money('EGP'),
                        TextEntry::make('shipping_fees')->label('الشحن')->money('EGP'),
                        TextEntry::make('total_amount')->label('الصافي')->money('EGP')->weight(FontWeight::Black)
                            ->extraAttributes(['class' => 'bg-emerald-600 text-white p-3 rounded-xl text-center shadow text-lg']),
                    ])->compact(),
                ]),

                // سكشن المتابعة (للتأجيلات)
                 Section::make('المتابعة')->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('created_at')->label('تاريخ الإنشاء')->dateTime(),
                        TextEntry::make('defers_count')->label('عدد التأجيلات')->badge()->color('danger'),
                        TextEntry::make('defer_reason')->label('سبب التأجيل')->color('danger')->placeholder('---'),
                    ])
                 ])->compact()->visible(fn($record) => $record->defers_count > 0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('التاريخ')->sortable(),
                Tables\Columns\TextColumn::make('tracking_number')->label('الكود')->copyable()->searchable(),
                Tables\Columns\TextColumn::make('branch.name')->label('الفرع')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge()->formatStateUsing(fn($state) => self::getTranslatedStatus($state)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // زر التعيين السريع في الجدول (تم إصلاحه ليفلتر حسب الفرع)
                Tables\Actions\Action::make('quick_assign')
                    ->label('تعيين')
                    ->icon('heroicon-m-user-plus')
                    ->color('warning')
                    ->visible(fn($record) => $record->status === 'in_stock')
                    ->form([
                        Forms\Components\Select::make('courier_id')
                            ->label('المندوب')
                            ->options(fn($record) => User::where('branch_id', $record->branch_id)->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (Shipment $record, array $data) {
                        $record->update(['courier_id' => $data['courier_id'], 'status' => 'assigned']);
                        Notification::make()->title('تم التعيين')->success()->send();
                    }),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات الشحنة')->schema([
                Forms\Components\Select::make('merchant_id')->relationship('merchant', 'name')->required()->label('التاجر'),
                Forms\Components\TextInput::make('customer_name')->required()->label('العميل'),
                Forms\Components\TextInput::make('customer_phone')->required()->label('الموبايل'),
                Forms\Components\Select::make('governorate_id')->relationship('governorate', 'name')->required()->label('المحافظة')->live(),
                Forms\Components\Select::make('area_id')->required()->label('المنطقة')
                    ->options(fn (Forms\Get $get) => Area::where('governorate_id', $get('governorate_id'))->pluck('name', 'id')),
                Forms\Components\TextInput::make('amount')->numeric()->label('المبلغ'),
                Forms\Components\Textarea::make('customer_address')->required()->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShipments::route('/'),
            'create' => Pages\CreateShipment::route('/create'),
            'view' => Pages\ViewShipment::route('/{record}'),
            'edit' => Pages\EditShipment::route('/{record}/edit'),
        ];
    }
}
