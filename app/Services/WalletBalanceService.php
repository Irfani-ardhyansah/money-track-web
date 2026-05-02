<?php

namespace App\Services;

use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WalletBalanceService
{
    /**
     * Return balance for a single wallet.
     * Balance = sum(income) + sum(incoming transfers) - sum(expense) - sum(outgoing transfers)
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
     * Return a keyed Collection [wallet_id => balance] for all non-deleted wallets.
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
     * Build hierarchical wallet tree with balances attached.
     * Returns only root wallets (parent_id = null) with nested `children`.
     */
    public function walletTree(): Collection
    {
        $balances = $this->allBalances();

        $all = Wallet::with('children')->whereNull('parent_id')->get();

        return $all->map(fn ($w) => $this->attachBalance($w, $balances));
    }

    private function attachBalance(Wallet $wallet, Collection $balances): Wallet
    {
        $wallet->balance = $balances->get($wallet->id, 0);
        $wallet->setRelation(
            'children',
            $wallet->children->map(fn ($c) => $this->attachBalance($c, $balances))
        );

        return $wallet;
    }
}
