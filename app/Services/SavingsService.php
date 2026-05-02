<?php

namespace App\Services;

use App\Models\SavingsAdjustment;
use Illuminate\Support\Collection;

class SavingsService
{
    public function __construct(private WalletBalanceService $walletBalanceService) {}

    /**
     * Sum of all root wallet transaction balances (with child rollup) — no double-count.
     */
    public function totalTracked(): float
    {
        return (float) $this->walletBalanceService
            ->walletTree()
            ->sum('balance');
    }

    /**
     * Sum of all manual savings adjustments.
     */
    public function totalManual(): float
    {
        return (float) SavingsAdjustment::sum('amount');
    }

    /**
     * Grand total = tracked wallet saldo + manual adjustments.
     */
    public function grandTotal(): float
    {
        return $this->totalTracked() + $this->totalManual();
    }

    /**
     * [wallet_id => sum_of_adjustments] for every wallet that has at least one adjustment.
     */
    public function adjustmentsByWallet(): Collection
    {
        return SavingsAdjustment::whereNotNull('wallet_id')
            ->selectRaw('wallet_id, SUM(amount) as total')
            ->groupBy('wallet_id')
            ->pluck('total', 'wallet_id')
            ->map(fn ($v) => (float) $v);
    }

    /**
     * Wallet tree where each wallet node also carries:
     *   ->adjustmentTotal  : sum of manual adjustments for that wallet only
     *   ->effectiveBalance : transactionBalance + adjustmentTotal
     *   ->adjustments      : Collection of SavingsAdjustment rows for that wallet
     *
     * Parent wallet effectiveBalance INCLUDES the child rollup on the transaction side,
     * but adjustment totals are per-wallet (not rolled up), so they display separately.
     */
    public function walletTreeWithAdjustments(): Collection
    {
        $walletTree = $this->walletBalanceService->walletTree();

        // Eager-load all adjustments keyed by wallet_id
        $adjustments = SavingsAdjustment::with('wallet')
            ->whereNotNull('wallet_id')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('wallet_id');

        $adjTotals = $this->adjustmentsByWallet();

        return $walletTree->map(function ($wallet) use ($adjustments, $adjTotals) {
            $adjTotal = (float) $adjTotals->get($wallet->id, 0);
            $wallet->adjustmentTotal   = $adjTotal;
            $wallet->effectiveBalance  = $wallet->balance + $adjTotal;
            $wallet->adjustments       = $adjustments->get($wallet->id, collect());

            $wallet->setRelation(
                'children',
                $wallet->children->map(function ($child) use ($adjustments, $adjTotals) {
                    $childAdjTotal = (float) $adjTotals->get($child->id, 0);
                    $child->adjustmentTotal  = $childAdjTotal;
                    $child->effectiveBalance = $child->balance + $childAdjTotal;
                    $child->adjustments      = $adjustments->get($child->id, collect());

                    return $child;
                })
            );

            return $wallet;
        });
    }
}
