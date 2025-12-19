<?php

namespace App\Filament\Resources\ShipmentResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WalletTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'walletTransactions'; // تأكد إن العلاقة دي موجودة في Shipment model
    protected static ?string $title = 'سجل المعاملات المالية (Finance Trail)';
    protected static ?string $icon = 'heroicon-o-banknotes';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('الوقت')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('wallet.owner.name')
                    ->label('المحفظة')
                    ->description(fn ($record) => class_basename($record->wallet->owner_type)) // يكتب Courier/Merchant
                    ->badge(),

                Tables\Columns\TextColumn::make('type')
                    ->label('نوع العملية')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->color(fn ($record) => $record->direction === 'credit' ? 'success' : 'danger')
                    ->prefix(fn ($record) => $record->direction === 'credit' ? '+ ' : '- ')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
