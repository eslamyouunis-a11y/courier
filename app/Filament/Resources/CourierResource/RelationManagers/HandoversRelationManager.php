<?php

namespace App\Filament\Resources\CourierResource\RelationManagers;

use App\Models\CourierHandover;
use App\Models\CourierHandoverItem;
use App\Models\Shipment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // ✅ مستخدمة بشكل صحيح

class HandoversRelationManager extends RelationManager
{
    protected static string $relationship = 'handovers';
    protected static ?string $title = 'سجل التصفيات (Handovers)';
    protected static ?string $icon = 'heroicon-o-clipboard-document-list';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('رقم #')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->date(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'confirmed' => 'success',
                    }),
                Tables\Columns\TextColumn::make('cod_total')->label('COD')->money('EGP')->weight('bold'),
                Tables\Columns\TextColumn::make('shipments_count')->label('شحنات'),
            ])
            ->headerActions([
                // ⚡ زرار إنشاء التصفية الذكي
                Tables\Actions\Action::make('create_handover')
                    ->label('إنشاء تصفية جديدة')
                    ->icon('heroicon-m-plus')
                    ->requiresConfirmation()
                    ->modalHeading('إنشاء تصفية عهدة جديدة')
                    ->modalDescription('سيقوم النظام بجمع كل الشحنات (المسلمة/المرتجعة) التي في عهدة المندوب ولم يتم تصفيتها بعد.')
                    ->action(function () {
                        $courier = $this->getOwnerRecord();

                        // 1. البحث عن الشحنات الجاهزة للتصفية
                        $eligibleShipments = Shipment::where('current_courier_id', $courier->id)
                            ->whereIn('status', [Shipment::STATUS_DELIVERED, Shipment::STATUS_RETURNED])
                            ->whereNull('courier_handover_id')
                            ->get();

                        if ($eligibleShipments->isEmpty()) {
                            Notification::make()
                                ->title('لا توجد شحنات جاهزة للتصفية')
                                ->warning()
                                ->send();
                            return;
                        }

                        DB::transaction(function () use ($courier, $eligibleShipments) {
                            // 2. إنشاء الهيدر
                            $handover = CourierHandover::create([
                                'courier_id' => $courier->id,
                                'branch_id'  => $courier->branch_id,
                                'status'     => 'open',
                                'created_by' => Auth::id(), // ✅ تعديل لتجنب تحذير المحرر
                            ]);

                            $totalCod = 0;

                            // 3. إضافة الشحنات للتصفية
                            foreach ($eligibleShipments as $shipment) {
                                // تحديد نوع البند
                                $type = match($shipment->status) {
                                    Shipment::STATUS_DELIVERED => 'delivered',
                                    Shipment::STATUS_RETURNED  => 'returned',
                                    default => 'delivered'
                                };

                                // الـ COD المتوقع
                                $codAmount = $shipment->status === Shipment::STATUS_DELIVERED ? $shipment->amount : 0;

                                CourierHandoverItem::create([
                                    // ❌ تم حذف 'deposit_id' لأنه غير موجود في الجدول وسيسبب خطأ SQL
                                    'handover_id' => $handover->id,
                                    'shipment_id' => $shipment->id,
                                    'item_type'   => $type,
                                    'cod_amount'  => $codAmount,
                                ]);

                                // ربط الشحنة بالتصفية
                                $shipment->update([
                                    'courier_handover_id' => $handover->id
                                ]);

                                $totalCod += $codAmount;
                            }

                            // تحديث الإجماليات
                            $handover->update([
                                'cod_total'       => $totalCod,
                                'shipments_count' => $eligibleShipments->count(),
                            ]);

                            Notification::make()
                                ->title('تم إنشاء التصفية بنجاح')
                                ->body("تم إدراج {$eligibleShipments->count()} شحنة بإجمالي {$totalCod} EGP")
                                ->success()
                                ->send();
                        });
                    }),
            ])
            ->actions([
                // زرار يوديك لصفحة التفاصيل عشان تعمل Confirm
                Tables\Actions\ViewAction::make()
                    // تأكد أن هذا الراوت صحيح ومطابق لما هو مسجل في filament
                    ->url(fn (CourierHandover $record): string => route('filament.admin.resources.courier-handovers.view', $record)),
            ]);
    }
}
