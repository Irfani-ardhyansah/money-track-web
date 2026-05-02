<?php

namespace App\Http\Controllers;

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
        $walletTree = $this->walletBalanceService->walletTree();

        return view('dashboard', compact('summary', 'breakdown', 'recent', 'walletTree', 'month', 'year'));
    }
}
