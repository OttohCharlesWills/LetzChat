<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function walletFor(User $user): Wallet
    {
        return Wallet::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Add funds to a wallet. Real payment gateway integration (Paystack/
     * Flutterwave) plugs in here later — right now this just credits the
     * balance directly, since payment processing wasn't in scope yet.
     */
    public function topUp(User $user, float $amount, ?string $reference = null): Wallet
    {
        return DB::transaction(function () use ($user, $amount, $reference) {
            $wallet = $this->walletFor($user);
            $wallet->increment('balance', $amount);
            $wallet->refresh();

            WalletTransaction::create([
                'wallet_id'     => $wallet->id,
                'type'          => 'topup',
                'amount'        => $amount,
                'balance_after' => $wallet->balance,
                'reference'     => $reference,
                'description'   => 'Wallet top-up',
            ]);

            return $wallet;
        });
    }

    /**
     * Escrow an ad's full budget upfront. Throws if insufficient balance.
     */
    public function escrowForAd(User $user, Ad $ad): void
    {
        DB::transaction(function () use ($user, $ad) {
            $wallet = $this->walletFor($user);

            if ($wallet->balance < $ad->budget) {
                throw new \RuntimeException('Insufficient wallet balance.');
            }

            $wallet->decrement('balance', $ad->budget);
            $wallet->refresh();

            WalletTransaction::create([
                'wallet_id'     => $wallet->id,
                'type'          => 'spend',
                'amount'        => $ad->budget,
                'balance_after' => $wallet->balance,
                'ad_id'         => $ad->id,
                'description'   => 'Ad budget escrow — ' . $ad->uuid,
            ]);
        });
    }

    /**
     * Refund whatever's left of an ad's budget when it ends (paused early
     * or completed with leftover budget from the CPM never fully spending).
     */
    public function refundRemaining(Ad $ad): void
    {
        $remaining = $ad->remainingBudget();

        if ($remaining <= 0) {
            return;
        }

        DB::transaction(function () use ($ad, $remaining) {
            $wallet = $this->walletFor($ad->advertiser);
            $wallet->increment('balance', $remaining);
            $wallet->refresh();

            WalletTransaction::create([
                'wallet_id'     => $wallet->id,
                'type'          => 'refund',
                'amount'        => $remaining,
                'balance_after' => $wallet->balance,
                'ad_id'         => $ad->id,
                'description'   => 'Unspent ad budget refunded — ' . $ad->uuid,
            ]);
        });
    }

        /**
     * Credit a wallet from a confirmed Paystack transaction. Safe to call
     * twice for the same reference (webhook + callback both fire) — the
     * unique constraint on wallet_transactions.reference makes the second
     * call a no-op instead of double-crediting.
     */
    public function creditFromPaystackReference(User $user, string $reference, float $amountInNaira): bool
    {
        if (WalletTransaction::where('reference', $reference)->exists()) {
            return false; // already processed
        }

        try {
            $this->topUp($user, $amountInNaira, $reference);
            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            // Race condition: webhook and callback landed at the same instant.
            // The unique index caught it — treat as already-processed, not an error.
            return false;
        }
    }
}