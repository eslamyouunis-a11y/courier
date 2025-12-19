<?php

namespace App\Services\Finance;

use App\Models\CourierHandover;
use Illuminate\Support\Facades\DB;
use LogicException;
use App\Services\Finance\WalletService;
use App\Services\Finance\FinanceTypes;

class CourierHandoverFinanceService
{
    public function __construct(
        private WalletService $wallets
    ) {}

    /**
     * Confirm courier handover:
     * - تصفية عهدة COD عند المندوب
     * - نقل رصيد الفرع (مع المناديب → خزنة)
     * - تسوية عمولة المندوب (خصم من الفرع)
     * - يمنع التأكيد مرتين
     */
    public function confirm(
        CourierHandover $handover,
        ?int $actorUserId = null
    ): void {
        // 🛑 حماية من double confirm
        if ($handover->status === 'confirmed') {
            throw new LogicException('Courier handover already confirmed');
        }

        DB::transaction(function () use ($handover, $actorUserId) {

            // Reload relations safely
            $handover->loadMissing([
                'courier.wallet',
                'branch.wallet',
                'items',
            ]);

            $courierWallet = $handover->courier->wallet()->firstOrFail();
            $branchWallet  = $handover->branch->wallet()->firstOrFail();

            /**
             * =========================
             * حساب القيم
             * =========================
             */

            // إجمالي COD من الشحنات المسلمة فقط
            $codCollected = (float) $handover->items
                ->where('item_type', 'delivered')
                ->sum('cod_amount');

            // عمولة المندوب (ثابتة × عدد الشحنات المسلمة)
            $commissionPerDelivered = (float) ($handover->courier->commission_delivered ?? 0);

            $deliveredCount = (int) $handover->items
                ->where('item_type', 'delivered')
                ->count();

            $commissionTotal = $commissionPerDelivered > 0
                ? $commissionPerDelivered * $deliveredCount
                : 0.0;

            /**
             * =========================
             * A) تصفية عهدة COD عند المندوب
             * =========================
             */
            if ($codCollected > 0) {

                // المندوب يسلم العهدة
                $this->wallets->debit(
                    wallet: $courierWallet,
                    amount: $codCollected,
                    type: FinanceTypes::COURIER_COD_ACCRUAL,
                    title: 'COD handed over',
                    notes: 'Courier handed over COD to branch',
                    shipmentId: null,
                    reference: $handover,
                    actorUserId: $actorUserId
                );

                /**
                 * نقل تصنيف رصيد الفرع
                 * WITH_COURIERS → IN_SAFE
                 */
                $this->wallets->debit(
                    wallet: $branchWallet,
                    amount: $codCollected,
                    type: FinanceTypes::BRANCH_WITH_COURIERS,
                    title: 'Cash moved from couriers',
                    notes: 'Cash reclassified from couriers to branch safe',
                    shipmentId: null,
                    reference: $handover,
                    actorUserId: $actorUserId
                );

                $this->wallets->credit(
                    wallet: $branchWallet,
                    amount: $codCollected,
                    type: FinanceTypes::BRANCH_IN_SAFE,
                    title: 'Cash received in safe',
                    notes: 'Cash now in branch safe',
                    shipmentId: null,
                    reference: $handover,
                    actorUserId: $actorUserId
                );
            }

            /**
             * =========================
             * B) تسوية عمولة المندوب
             * =========================
             */
            if ($commissionTotal > 0) {

                // تسوية استحقاق العمولة عند المندوب
                $this->wallets->debit(
                    wallet: $courierWallet,
                    amount: $commissionTotal,
                    type: FinanceTypes::COURIER_COMMISSION_DELIVERED,
                    title: 'Commission settled',
                    notes: 'Courier commission settled on handover',
                    shipmentId: null,
                    reference: $handover,
                    actorUserId: $actorUserId
                );

                // خصم العمولة من الفرع
                $this->wallets->debit(
                    wallet: $branchWallet,
                    amount: $commissionTotal,
                    type: FinanceTypes::BRANCH_COURIER_COMMISSION_PAID,
                    title: 'Courier commission paid',
                    notes: 'Branch paid courier commission',
                    shipmentId: null,
                    reference: $handover,
                    actorUserId: $actorUserId
                );
            }

            /**
             * =========================
             * تحديث بيانات المهمة
             * =========================
             */
            $handover->update([
                'status'          => 'confirmed',
                'shipments_count' => (int) $handover->items()->count(),
                'cod_total'       => $codCollected,
                'confirmed_by'    => $actorUserId,
                'confirmed_at'    => now(),
            ]);
        });
    }
}
