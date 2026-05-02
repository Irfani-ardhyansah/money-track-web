<?php

namespace App\Services;

use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WalletBalanceService
{
    /**
     * Return balance for a single wallet (direct transactions only, no children).
     */
    public function balanceFor(Wallet $wallet): float
    {
        $income = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('type', 'income')
            ->sum('amount');

        $expense = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('type', 'expense')
            ->sum('amount');

        $transferOut = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('type', 'transfer')
            ->sum('amount');

        $transferIn = DB::table('transactions')
            ->where('to_wallet_id', $wallet->id)
            ->where('type', 'transfer')
            ->sum('amount');

        return (float) ($income + $transferIn - $expense - $transferOut);
    }

    /**
     * Return a keyed Collection [wallet_id => direct_balance] for all non-deleted wallets.
     * Each wallet's balance is based solely on transactions assigned directly to that wallet.
     */
    public function allBalances(): Collection
    {
        $walletIds = Wallet::pluck('id');

        $income = DB::table('transactions')
            ->whereIn('wallet_id', $walletIds)
            ->where('type', 'income')
            ->groupBy('wallet_id')
            ->select('wallet_id', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'wallet_id');

        $expense = DB::table('transactions')
            ->whereIn('wallet_id', $walletIds)
            ->where('type', 'expense')
            ->groupBy('wallet_id')
            ->select('wallet_id', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'wallet_id');

        $transferOut = DB::table('transactions')
            ->whereIn('wallet_id', $walletIds)
            ->where('type', 'transfer')
            ->groupBy('wallet_id')
            ->select('wallet_id', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'wallet_id');

        $transferIn = DB::table('transactions')
            ->whereIn('to_wallet_id', $walletIds)
            ->where('type', 'transfer')
            ->groupBy('to_wallet_id')
            ->select('to_wallet_id', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'to_wallet_id');

        return $walletIds->mapWithKeys(function ($id) use ($income, $expense, $transferOut, $transferIn) {
            $balance = (float) ($income->get($id, 0)
                + $transferIn->get($id, 0)
                - $expense->get($id, 0)
                - $transferOut->get($id, 0));

            return [$id => $balance];
        });
    }

    /**
     * Return [wallet_id => balance] where each parent wallet's balance
     * includes the sum of all its direct children's balances.
     * Child wallet entries remain unchanged (direct balance only).
     */
    public function allBalancesWithRollup(): Collection
    {
        return $this->rollupBalances($this->allBalances());
    }

    /**
     * Build hierarchical wallet tree with balances attached.
     * - Parent wallet balance = own direct balance + sum of all children's direct balances.
     * - Child wallet balance  = own direct balance only.
     */
    public function walletTree(): Collection
    {
        $direct = $this->allBalances();
        $rolled = $this->rollupBalances($direct);

        $all = Wallet::with('children')->whereNull('parent_id')->get();

        return $all->map(function (Wallet $wallet) use ($direct, $rolled) {
            $wallet->balance = $rolled->get($wallet->id, 0);

            $wallet->setRelation(
                'children',
                $wallet->children->map(function (Wallet $child) use ($direct) {
                    $child->balance = $direct->get($child->id, 0);

                    return $child;
                })
            );

            return $wallet;
        });
    }

    /**
     * Wallet tree with net activity for the given month only (not cumulative).
     * income + transfer_in - expense - transfer_out within that month/year.
     * Wallets with no activity in the month will show 0.
     */
    public function walletTreeForMonth(int $month, int $year): Collection
    {
        $walletIds = Wallet::pluck('id');

        $base = fn ($query) => $query
            ->whereYear('occurred_at', $year)
            ->whereMonth('occurred_at', $month);

        $income = DB::table('transactions')
            ->whereIn('wallet_id', $walletIds)
            ->where('type', 'income')
            ->when(true, $base)
            ->groupBy('wallet_id')
            ->select('wallet_id', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'wallet_id');

        $expense = DB::table('transactions')
            ->whereIn('wallet_id', $walletIds)
            ->where('type', 'expense')
            ->when(true, $base)
            ->groupBy('wallet_id')
            ->select('wallet_id', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'wallet_id');

        $transferOut = DB::table('transactions')
            ->whereIn('wallet_id', $walletIds)
            ->where('type', 'transfer')
            ->when(true, $base)
            ->groupBy('wallet_id')
            ->select('wallet_id', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'wallet_id');

        $transferIn = DB::table('transactions')
            ->whereIn('to_wallet_id', $walletIds)
            ->where('type', 'transfer')
            ->when(true, $base)
            ->groupBy('to_wallet_id')
            ->select('to_wallet_id', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'to_wallet_id');

        $direct = $walletIds->mapWithKeys(function ($id) use ($income, $expense, $transferOut, $transferIn) {
            return [$id => (float) (
                $income->get($id, 0)
                + $transferIn->get($id, 0)
                - $expense->get($id, 0)
                - $transferOut->get($id, 0)
            )];
        });

        $rolled = $this->rollupBalances($direct);
        $all    = Wallet::with('children')->whereNull('parent_id')->get();

        return $all->map(function (Wallet $wallet) use ($direct, $rolled) {
            $wallet->balance = $rolled->get($wallet->id, 0);

            $wallet->setRelation(
                'children',
                $wallet->children->map(function (Wallet $child) use ($direct) {
                    $child->balance = $direct->get($child->id, 0);

                    return $child;
                })
            );

            return $wallet;
        });
    }

    /**
     * Given a [wallet_id => balance] collection, return a new collection where
     * each parent wallet's value is augmented by the sum of its children's values.
     */
    private function rollupBalances(Collection $direct): Collection
    {
        // [child_id => parent_id] for wallets that have a parent
        $childToParent = Wallet::whereNotNull('parent_id')
            ->pluck('parent_id', 'id');

        $rolled = $direct->map(fn ($b) => $b); // shallow copy

        foreach ($childToParent as $childId => $parentId) {
            if ($rolled->has($parentId)) {
                $rolled[$parentId] = $rolled->get($parentId, 0)
                    + $direct->get($childId, 0);
            }
        }

        return $rolled;
    }
}
