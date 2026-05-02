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
     * Only `expense` transactions are included; transfer is excluded.
     * Returns a collection of objects with: category, amount, percentage.
     */
    public function categoryBreakdown(int $month, int $year): Collection
    {
        $rows = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->whereYear('transactions.occurred_at', $year)
            ->whereMonth('transactions.occurred_at', $month)
            ->where('transactions.type', 'expense')
            ->whereNull('categories.deleted_at')
            ->groupBy('transactions.category_id', 'categories.name')
            ->select(
                'transactions.category_id',
                'categories.name as category_name',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->orderByDesc('total')
            ->get();

        $grandTotal = $rows->sum('total');

        return $rows->map(function ($row) use ($grandTotal) {
            return (object) [
                'category_id' => $row->category_id,
                'category_name' => $row->category_name,
                'amount' => (float) $row->total,
                'percentage' => $grandTotal > 0
                    ? round(($row->total / $grandTotal) * 100, 1)
                    : 0,
            ];
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
