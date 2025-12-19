<?php

namespace App\Filament\Resources\CourierResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'shipments'; // اسم العلاقة في الموديل
    protected static ?string $title = 'سجل الشحنات';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tracking_number')
            ->columns([
                Tables\Columns\TextColumn::make('tracking_number')->label('البوليصة')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
                Tables\Columns\TextColumn::make('amount')->label('COD')->money('EGP'),
                Tables\Columns\TextColumn::make('area.name')->label('المنطقة'),
                Tables\Columns\TextColumn::make('updated_at')->label('آخر تحديث')->date(),
            ])
            ->filters([
                // فلتر يعرض الشحنات اللي في عهدته حالياً بس
                Tables\Filters\Filter::make('current_custody')
                    ->label('في العهدة حالياً')
                    ->query(fn ($query) => $query->where('current_courier_id', $this->getOwnerRecord()->id))
                    ->default(), // مفعل افتراضياً
            ]);
    }
}
