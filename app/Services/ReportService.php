<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Monthly income / expense / net summary.
     * Transfer is excluded from both income and expense totals.
     */
    public function monthlySummary(int $month, int $year): array
    {
        $income = Transaction::whereYear('occurred_at', $year)
            ->whereMonth('occurred_at', $month)
            ->where('type', 'income')
            ->sum('amount');

        $expense = Transaction::whereYear('occurred_at', $year)
            ->whereMonth('occurred_at', $month)
            ->where('type', 'expense')
            ->sum('amount');

        return [
            'income' => (float) $income,
            'expense' => (float) $expense,
            'net' => (float) ($income - $expense),
        ];
    }

    /**
     * Category breakdown for a given month.
     *
     * @param string $walletFilter  'all' | 'regular' | 'savings'
     *   - all     : expense (all wallets, rose) + transfer to savings/investment wallets (violet)
     *   - regular : expense transactions only (non-savings wallets)
     *   - savings : transfer transactions whose destination is a savings/investment wallet
     *
     * Each returned object has:
     *   category_id    – numeric category ID for expenses, null for transfer-to-savings rows
     *   category_name  – category name or destination wallet name
     *   amount         – float total
     *   percentage     – float percentage of grand total
     *   entry_type     – 'expense' | 'transfer_savings'
     */
    public function categoryBreakdown(int $month, int $year, string $walletFilter = 'all'): Collection
    {
        $savingsTypes = ['savings', 'investment'];
        $items        = collect();

        // --- expense rows ---
        if (in_array($walletFilter, ['all', 'regular'])) {
            $expQuery = DB::table('transactions')
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
                ->whereYear('transactions.occurred_at', $year)
                ->whereMonth('transactions.occurred_at', $month)
                ->where('transactions.type', 'expense')
                ->whereNull('categories.deleted_at')
                ->whereNull('wallets.deleted_at');

            if ($walletFilter === 'regular') {
                $expQuery->whereNotIn('wallets.type', $savingsTypes);
            }

            $expRows = $expQuery
                ->groupBy('transactions.category_id', 'categories.name')
                ->select(
                    'transactions.category_id',
                    'categories.name as category_name',
                    DB::raw('SUM(transactions.amount) as total')
                )
                ->get()
                ->map(fn ($r) => (object) [
                    'category_id'   => (int) $r->category_id,
                    'category_name' => $r->category_name,
                    'amount'        => (float) $r->total,
                    'entry_type'    => 'expense',
                ]);

            $items = $items->merge($expRows);
        }

        // --- transfer-to-savings rows (grouped by destination wallet) ---
        if (in_array($walletFilter, ['all', 'savings'])) {
            $trRows = DB::table('transactions')
                ->join('wallets as tw', 'transactions.to_wallet_id', '=', 'tw.id')
                ->whereYear('transactions.occurred_at', $year)
                ->whereMonth('transactions.occurred_at', $month)
                ->where('transactions.type', 'transfer')
                ->whereIn('tw.type', $savingsTypes)
                ->whereNull('tw.deleted_at')
                ->groupBy('transactions.to_wallet_id', 'tw.name')
                ->select(
                    'transactions.to_wallet_id',
                    'tw.name as wallet_name',
                    DB::raw('SUM(transactions.amount) as total')
                )
                ->get()
                ->map(fn ($r) => (object) [
                    'category_id'   => null,
                    'category_name' => $r->wallet_name,
                    'amount'        => (float) $r->total,
                    'entry_type'    => 'transfer_savings',
                ]);

            $items = $items->merge($trRows);
        }

        $grandTotal = $items->sum('amount');

        return $items
            ->sortByDesc('amount')
            ->values()
            ->map(function ($item) use ($grandTotal) {
                $item->percentage = $grandTotal > 0
                    ? round(($item->amount / $grandTotal) * 100, 1)
                    : 0;

                return $item;
            });
    }

    /**
     * Recent transactions for dashboard preview (latest N).
     */
    public function recentTransactions(int $limit = 5): Collection
    {
        return Transaction::with(['wallet', 'toWallet', 'category'])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
