<?php

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

function walletBalance(Wallet $wallet): float
{
    return WalletTransaction::where('wallet_id', $wallet->id)
        ->sum(DB::raw("
            CASE
                WHEN direction = 'credit' THEN amount
                ELSE -amount
            END
        "));
}
