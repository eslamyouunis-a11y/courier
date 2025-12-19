<?php

namespace App\Services\Finance;

final class FinanceTypes
{
    // Courier
    public const COURIER_COD_ACCRUAL        = 'courier_cod_accrual';        // عند التسليم: المندوب عليه عهدة COD
    public const COURIER_COMMISSION_ACCRUAL = 'courier_commission_accrual'; // عند التسليم: عمولة مستحقة للمندوب
    public const COURIER_COD_HANDOVER       = 'courier_cod_handover';       // عند الاستلام: تصفية COD من المندوب
    public const COURIER_COMMISSION_PAYOUT  = 'courier_commission_payout';  // عند الاستلام: صرف عمولة للمندوب

    // Branch
    public const BRANCH_TOTAL_ACCRUAL       = 'branch_total_accrual';       // عند التسليم: إجمالي مسؤولية الفرع يزيد
    public const BRANCH_WITH_COURIERS       = 'branch_with_couriers';       // عند التسليم: رصيد مع المناديب يزيد
    public const BRANCH_WITH_COURIERS_CLEAR = 'branch_with_couriers_clear'; // عند الاستلام: تقليل رصيد مع المناديب (نقل للخزنة)
    public const BRANCH_COMMISSION_EXPENSE  = 'branch_commission_expense';  // عند الاستلام: عمولة خرجت من الفرع (خصم من الإجمالي)
    public const BRANCH_DEPOSIT_OUT         = 'branch_deposit_out';         // عند الإيداع: خصم من إجمالي الفرع

    // Company
    public const COMPANY_DEPOSIT_IN         = 'company_deposit_in';         // عند الإيداع: الشركة تستلم من الفرع
    public const COMPANY_PAYOUT_OUT         = 'company_payout_out';         // عند توريد التاجر: الشركة تدفع

    // Merchant
    public const MERCHANT_TOTAL_EARNING     = 'merchant_total_earning';     // عند التسليم: إجمالي مستحقات
    public const MERCHANT_AVAILABLE_MOVE_IN = 'merchant_available_in';      // بعد المدة: يدخل في القابل للتوريد
    public const MERCHANT_TOTAL_MOVE_OUT    = 'merchant_total_out';         // بعد المدة: يخرج من الإجمالي
    public const MERCHANT_PAYOUT_OUT        = 'merchant_payout_out';        // عند التوريد: يخرج من القابل للتوريد
    public const MERCHANT_PAYOUT_IN = 'merchant_payout_in';

    public const COURIER_COMMISSION_DELIVERED = 'courier_commission_delivered';
    public const COURIER_COMMISSION_RETURNED_PAID = 'courier_commission_returned_paid';
    public const COURIER_COMMISSION_RETURNED_SENDER = 'courier_commission_returned_sender';

    public const MERCHANT_SHIPPING_FEE_CHARGE = 'merchant_shipping_fee_charge';
    public const COMPANY_SHIPPING_FEE_INCOME = 'company_shipping_fee_income';
    public const BRANCH_WITH_COURIERS_REDUCTION = 'branch_with_couriers_reduction';
    public const BRANCH_COURIER_COMMISSION_EXPENSE = 'branch_courier_commission_expense';
    public const BRANCH_IN_SAFE       = 'branch_in_safe';
    public const BRANCH_COURIER_COMMISSION_PAID = 'branch_courier_commission_paid';
    public const WALLET_ADJUSTMENT_IN  = 'wallet_adjustment_in';
    public const WALLET_ADJUSTMENT_OUT = 'wallet_adjustment_out';
    public const RETURN_SHIPPING_DEDUCTION = 'return_shipping_deduction';

}
