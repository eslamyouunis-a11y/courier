<?php

namespace App\Filament\Resources\BranchResource\RelationManagers;

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
            ->recordTitleAttribute('code')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('الباركود')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('courier.name')
                    ->label('المندوب')
                    ->placeholder('في المخزن')
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('المبلغ')
                    ->money('EGP'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered' => 'success',
                        'out_for_delivery' => 'warning',
                        'returned' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'saved' => 'في المخزن',
                        'out_for_delivery' => 'توزيع',
                        'delivered' => 'تم التسليم',
                        'returned' => 'مرتجع',
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
