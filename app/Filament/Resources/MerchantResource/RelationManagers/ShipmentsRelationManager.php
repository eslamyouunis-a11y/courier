<?php

namespace App\Filament\Resources\MerchantResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'shipments';
    protected static ?string $title = 'أرشيف الشحنات';
    protected static ?string $icon = 'heroicon-m-cube';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tracking_number')
            ->columns([
                Tables\Columns\TextColumn::make('tracking_number')->label('رقم الشحنة')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
                Tables\Columns\TextColumn::make('total_amount')->label('المبلغ')->money('EGP'),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime('d/m/Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'saved' => 'جديد',
                        'delivered' => 'تم التسليم',
                        'returned' => 'مرتجع',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
