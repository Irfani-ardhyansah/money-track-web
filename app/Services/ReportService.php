<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Monthly income / expense / net summary.
     * Transfer to savings/investment wallets is tracked separately as `transfer_savings`.
     * Transfer to wallets belonging to other top-level owners is tracked as `transfer_others`.
     *
     * @param int[]|null $ownerWalletIds  Limit to these wallet IDs (null = all).
     * @param int[]|null $allOwnerIds     All wallet IDs of the selected owner (used to detect cross-owner transfers).
     */
    public function monthlySummary(int $month, int $year, ?array $ownerWalletIds = null, ?array $allOwnerIds = null): array
    {
        $savingsTypes = ['savings', 'investment'];

        $income = Transaction::whereYear('occurred_at', $year)
            ->whereMonth('occurred_at', $month)
            ->where('type', 'income')
            ->when($ownerWalletIds, fn ($q) => $q->whereIn('wallet_id', $ownerWalletIds))
            ->sum('amount');

        $expense = Transaction::whereYear('occurred_at', $year)
            ->whereMonth('occurred_at', $month)
            ->where('type', 'expense')
            ->when($ownerWalletIds, fn ($q) => $q->whereIn('wallet_id', $ownerWalletIds))
            ->sum('amount');

        // Gross transfers TO savings wallets this month.
        $savingsDeposits = (float) DB::table('transactions')
            ->join('wallets as tw', 'transactions.to_wallet_id', '=', 'tw.id')
            ->whereYear('transactions.occurred_at', $year)
            ->whereMonth('transactions.occurred_at', $month)
            ->where('transactions.type', 'transfer')
            ->whereIn('tw.type', $savingsTypes)
            ->whereNull('tw.deleted_at')
            ->when($ownerWalletIds, fn ($q) => $q->whereIn('transactions.wallet_id', $ownerWalletIds))
            ->sum('transactions.amount');

        // Withdrawals FROM savings wallets back to regular wallets this month.
        // Subtract to get net savings allocation, consistent with the wallet balance card.
        $savingsWithdrawals = (float) DB::table('transactions')
            ->join('wallets as sw', 'transactions.wallet_id', '=', 'sw.id')
            ->whereYear('transactions.occurred_at', $year)
            ->whereMonth('transactions.occurred_at', $month)
            ->where('transactions.type', 'transfer')
            ->whereIn('sw.type', $savingsTypes)
            ->whereNull('sw.deleted_at')
            ->when($ownerWalletIds, fn ($q) => $q->whereIn('transactions.wallet_id', $ownerWalletIds))
            ->sum('transactions.amount');

        $transferSavings = $savingsDeposits - $savingsWithdrawals;

        // Transfers from this owner's wallets to wallets belonging to other top-level owners.
        // Only computed when a specific owner is selected.
        $transferOthers       = 0.0;
        $transferOthersDetail = [];

        if ($ownerWalletIds && $allOwnerIds) {
            $rows = DB::table('transactions')
                ->join('wallets as tw', 'transactions.to_wallet_id', '=', 'tw.id')
                ->leftJoin('wallets as tpw', 'tw.parent_id', '=', 'tpw.id')
                ->whereYear('transactions.occurred_at', $year)
                ->whereMonth('transactions.occurred_at', $month)
                ->where('transactions.type', 'transfer')
                ->whereIn('transactions.wallet_id', $ownerWalletIds)
                ->whereNotIn('transactions.to_wallet_id', $allOwnerIds)
                ->whereNotIn('tw.type', $savingsTypes)
                ->whereNull('tw.deleted_at')
                ->groupBy(DB::raw('COALESCE(tw.parent_id, tw.id)'), DB::raw('COALESCE(tpw.name, tw.name)'))
                ->select(
                    DB::raw('COALESCE(tpw.name, tw.name) as dest_owner'),
                    DB::raw('SUM(transactions.amount) as total')
                )
                ->get();

            $transferOthers       = (float) $rows->sum('total');
            $transferOthersDetail = $rows->map(fn ($r) => [
                'name'   => $r->dest_owner,
                'amount' => (float) $r->total,
            ])->values()->all();
        }

        // Transfers INTO this owner's wallets from other top-level owners (cross-owner inflow).
        // Only computed when a specific owner is selected.
        $transferInOthers       = 0.0;
        $transferInOthersDetail = [];

        if ($ownerWalletIds && $allOwnerIds) {
            $rows = DB::table('transactions')
                ->join('wallets as sw', 'transactions.wallet_id', '=', 'sw.id')
                ->leftJoin('wallets as spw', 'sw.parent_id', '=', 'spw.id')
                ->whereYear('transactions.occurred_at', $year)
                ->whereMonth('transactions.occurred_at', $month)
                ->where('transactions.type', 'transfer')
                ->whereIn('transactions.to_wallet_id', $ownerWalletIds)
                ->whereNotIn('transactions.wallet_id', $allOwnerIds)
                ->whereNull('sw.deleted_at')
                ->groupBy(DB::raw('COALESCE(sw.parent_id, sw.id)'), DB::raw('COALESCE(spw.name, sw.name)'))
                ->select(
                    DB::raw('COALESCE(spw.name, sw.name) as source_owner'),
                    DB::raw('SUM(transactions.amount) as total')
                )
                ->get();

            $transferInOthers       = (float) $rows->sum('total');
            $transferInOthersDetail = $rows->map(fn ($r) => [
                'name'   => $r->source_owner,
                'amount' => (float) $r->total,
            ])->values()->all();
        }

        // Income breakdown by top-level owner — only computed when no owner filter (for "Semua" tooltip).
        $incomeDetail = [];
        if (! $ownerWalletIds) {
            $rows = DB::table('transactions')
                ->join('wallets as sw', 'transactions.wallet_id', '=', 'sw.id')
                ->leftJoin('wallets as spw', 'sw.parent_id', '=', 'spw.id')
                ->whereYear('transactions.occurred_at', $year)
                ->whereMonth('transactions.occurred_at', $month)
                ->where('transactions.type', 'income')
                ->whereNull('sw.deleted_at')
                ->groupBy(DB::raw('COALESCE(sw.parent_id, sw.id)'), DB::raw('COALESCE(spw.name, sw.name)'))
                ->select(
                    DB::raw('COALESCE(spw.name, sw.name) as owner_name'),
                    DB::raw('SUM(transactions.amount) as total')
                )
                ->orderByDesc('total')
                ->get();

            $incomeDetail = $rows->map(fn ($r) => [
                'name'   => $r->owner_name,
                'amount' => (float) $r->total,
            ])->values()->all();
        }

        return [
            'income'                    => (float) $income,
            'income_detail'             => $incomeDetail,
            'expense'                   => (float) $expense,
            'transfer_savings'          => $transferSavings,
            'transfer_others'           => $transferOthers,
            'transfer_others_detail'    => $transferOthersDetail,
            'transfer_in_others'        => $transferInOthers,
            'transfer_in_others_detail' => $transferInOthersDetail,
            'net'                       => (float) ($income + $transferInOthers - $expense - $transferSavings - $transferOthers),
        ];
    }

    /**
     * Category breakdown for a given month.
     *
     * @param string     $walletFilter   'all' | 'regular' | 'savings'
     * @param int[]|null $ownerWalletIds Limit to these wallet IDs (null = all).
     */
    public function categoryBreakdown(int $month, int $year, string $walletFilter = 'all', ?array $ownerWalletIds = null): Collection
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
                ->whereNull('wallets.deleted_at')
                ->when($ownerWalletIds, fn ($q) => $q->whereIn('transactions.wallet_id', $ownerWalletIds));

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

        // --- transfer-to-savings rows (grouped by destination wallet + source parent wallet) ---
        if (in_array($walletFilter, ['all', 'savings'])) {
            $trRows = DB::table('transactions')
                ->join('wallets as tw', 'transactions.to_wallet_id', '=', 'tw.id')
                ->join('wallets as sw', 'transactions.wallet_id', '=', 'sw.id')
                ->leftJoin('wallets as spw', 'sw.parent_id', '=', 'spw.id')
                ->whereYear('transactions.occurred_at', $year)
                ->whereMonth('transactions.occurred_at', $month)
                ->where('transactions.type', 'transfer')
                ->whereIn('tw.type', $savingsTypes)
                ->whereNull('tw.deleted_at')
                ->whereNull('sw.deleted_at')
                ->when($ownerWalletIds, fn ($q) => $q->whereIn('transactions.wallet_id', $ownerWalletIds))
                ->groupBy('transactions.to_wallet_id', 'tw.name', 'sw.parent_id', 'sw.name', 'spw.name')
                ->select(
                    'transactions.to_wallet_id',
                    'tw.name as wallet_name',
                    'sw.parent_id as source_parent_id',
                    DB::raw('COALESCE(spw.name, sw.name) as source_parent_name'),
                    DB::raw('SUM(transactions.amount) as total')
                )
                ->get();

            // Outflows FROM savings wallets (e.g. withdrawals back to regular wallets) in the same period.
            // Subtracting these gives the net savings movement, consistent with the wallet balance card.
            $savingsOutflows = DB::table('transactions')
                ->join('wallets as sw', 'transactions.wallet_id', '=', 'sw.id')
                ->whereYear('transactions.occurred_at', $year)
                ->whereMonth('transactions.occurred_at', $month)
                ->where('transactions.type', 'transfer')
                ->whereIn('sw.type', $savingsTypes)
                ->whereNull('sw.deleted_at')
                ->when($ownerWalletIds, fn ($q) => $q->whereIn('transactions.wallet_id', $ownerWalletIds))
                ->groupBy('transactions.wallet_id')
                ->select('transactions.wallet_id', DB::raw('SUM(transactions.amount) as total_out'))
                ->pluck('total_out', 'wallet_id')
                ->map(fn ($v) => (float) $v);

            // Total inflow per savings wallet — used to split outflow proportionally
            // when the same wallet received transfers from multiple source owners.
            $inTotals = $trRows->groupBy('to_wallet_id')
                ->map(fn ($rows) => (float) $rows->sum('total'));

            $trRows = $trRows
                ->map(function ($r) use ($savingsOutflows, $inTotals) {
                    $outflow  = $savingsOutflows->get($r->to_wallet_id, 0.0);
                    $inTotal  = $inTotals->get($r->to_wallet_id, 0.0);
                    $rowShare = $inTotal > 0 ? ((float) $r->total / $inTotal) : 1.0;
                    $adjusted = (float) $r->total - ($outflow * $rowShare);

                    return (object) [
                        'category_id'        => null,
                        'category_name'      => $r->wallet_name,
                        'source_parent_name' => $r->source_parent_name,
                        'amount'             => $adjusted,
                        'entry_type'         => 'transfer_savings',
                    ];
                })
                ->filter(fn ($r) => $r->amount > 0)
                ->values();

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
     *
     * @param int[]|null $ownerWalletIds Limit to these wallet IDs (null = all).
     */
    public function recentTransactions(int $limit = 5, ?array $ownerWalletIds = null): Collection
    {
        return Transaction::with(['wallet', 'toWallet', 'category'])
            ->when($ownerWalletIds, fn ($q) => $q->whereIn('wallet_id', $ownerWalletIds))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
