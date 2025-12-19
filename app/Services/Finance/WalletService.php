<?php

namespace App\Services\Finance;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /**
     * Credit wallet (increase balance)
     */
    public function credit(
        Wallet $wallet,
        float $amount,
        string $type,
        ?string $title = null,
        ?string $notes = null,
        ?int $shipmentId = null,
        ?object $reference = null,
        ?int $actorUserId = null,
        ?string $occurredAt = null
    ): WalletTransaction {
        return $this->post(
            wallet: $wallet,
            direction: 'credit',
            amount: $amount,
            type: $type,
            title: $title,
            notes: $notes,
            shipmentId: $shipmentId,
            reference: $reference,
            actorUserId: $actorUserId,
            occurredAt: $occurredAt
        );
    }

    /**
     * Debit wallet (decrease balance)
     */
    public function debit(
        Wallet $wallet,
        float $amount,
        string $type,
        ?string $title = null,
        ?string $notes = null,
        ?int $shipmentId = null,
        ?object $reference = null,
        ?int $actorUserId = null,
        ?string $occurredAt = null
    ): WalletTransaction {
        return $this->post(
            wallet: $wallet,
            direction: 'debit',
            amount: $amount,
            type: $type,
            title: $title,
            notes: $notes,
            shipmentId: $shipmentId,
            reference: $reference,
            actorUserId: $actorUserId,
            occurredAt: $occurredAt
        );
    }

    /**
     * Core ledger posting logic
     */
    private function post(
        Wallet $wallet,
        string $direction,
        float $amount,
        string $type,
        ?string $title,
        ?string $notes,
        ?int $shipmentId,
        ?object $reference,
        ?int $actorUserId,
        ?string $occurredAt
    ): WalletTransaction {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Amount must be greater than zero',
            ]);
        }

        return DB::transaction(function () use (
            $wallet,
            $direction,
            $amount,
            $type,
            $title,
            $notes,
            $shipmentId,
            $reference,
            $actorUserId,
            $occurredAt
        ) {
            // Lock wallet row (important for concurrency)
            $lockedWallet = Wallet::whereKey($wallet->id)
                ->lockForUpdate()
                ->firstOrFail();

            /**
             * ✅ Safe created_by handling
             * - لو User موجود فعليًا → نستخدمه
             * - غير كده → null (system / background action)
             */
            $safeCreatedBy = null;
            if ($actorUserId && User::whereKey($actorUserId)->exists()) {
                $safeCreatedBy = $actorUserId;
            }

            $transaction = WalletTransaction::create([
                'wallet_id'      => $lockedWallet->id,
                'direction'      => $direction,
                'type'           => $type,
                'amount'         => $amount,
                'shipment_id'    => $shipmentId,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id'   => $reference?->id,
                'title'          => $title,
                'notes'          => $notes,
                'created_by'     => $safeCreatedBy,
                'occurred_at'    => $occurredAt,
            ]);

            /**
             * Cached balance update
             * (لو حابب تعتمد 100% على ledger، تقدر تشيل السطرين دول)
             */
            $delta = $direction === 'credit' ? $amount : -$amount;
            $lockedWallet->balance = (float) $lockedWallet->balance + $delta;
            $lockedWallet->save();

            return $transaction;
        });
    }

    /**
     * Company main wallet
     */
    public function getCompanyWallet(): Wallet
    {
        return Wallet::where('owner_type', 'company')
            ->whereNull('owner_id')
            ->firstOrFail();
    }
}
