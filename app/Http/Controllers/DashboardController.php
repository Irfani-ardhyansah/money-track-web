<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ReportService;
use App\Services\WalletBalanceService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private WalletBalanceService $walletBalanceService,
    ) {}

    public function index(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $summary = $this->reportService->monthlySummary($month, $year);
        $breakdown = $this->reportService->categoryBreakdown($month, $year);
        $recent = $this->reportService->recentTransactions(10);
        $walletTree = $this->walletBalanceService->walletTreeForMonth($month, $year);

        return view('dashboard', compact('summary', 'breakdown', 'recent', 'walletTree', 'month', 'year'));
    }

    public function categoryTransactions(Request $request)
    {
        $month      = (int) $request->input('month', now()->month);
        $year       = (int) $request->input('year', now()->year);
        $categoryId = $request->input('category_id');

        $transactions = Transaction::with(['wallet.parent'])
            ->where('category_id', $categoryId)
            ->where('type', 'expense')
            ->whereYear('occurred_at', $year)
            ->whereMonth('occurred_at', $month)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        return response()->json(
            $transactions->map(fn ($tx) => [
                'id'     => $tx->id,
                'date'   => $tx->occurred_at->translatedFormat('d M Y'),
                'wallet' => $tx->wallet->parent
                    ? $tx->wallet->parent->name . ' › ' . $tx->wallet->name
                    : $tx->wallet->name,
                'amount' => $tx->amount,
                'notes'  => $tx->notes,
            ])
        );
    }
}
