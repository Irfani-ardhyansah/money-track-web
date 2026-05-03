<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
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
        $year  = (int) $request->query('year', now()->year);

        $breakdownFilter = in_array($request->query('breakdown_filter'), ['all', 'regular', 'savings'])
            ? $request->query('breakdown_filter')
            : 'all';

        // Root wallet filter (e.g. "Suami", "Istri")
        $ownerId     = $request->query('owner_id') ? (int) $request->query('owner_id') : null;
        $rootWallets = Wallet::whereNull('parent_id')->orderBy('name')->get();

        // Derive all wallet IDs belonging to the selected owner (owner + its children)
        $ownerWalletIds = null;
        $allOwnerIds    = null;
        if ($ownerId) {
            $ownerWalletIds = Wallet::where('id', $ownerId)
                ->orWhere('parent_id', $ownerId)
                ->pluck('id')
                ->toArray();
            $allOwnerIds = $ownerWalletIds; // same set; used to detect cross-owner transfers
        }

        $summary   = $this->reportService->monthlySummary($month, $year, $ownerWalletIds, $allOwnerIds);
        $breakdown = $this->reportService->categoryBreakdown($month, $year, $breakdownFilter, $ownerWalletIds);
        $recent    = $this->reportService->recentTransactions(10, $ownerWalletIds);

        $savingsTypes = ['savings', 'investment'];

        // Monthly activity — only regular (spendable) wallets, filtered by owner if set
        $walletTreeMonth = $this->walletBalanceService
            ->walletTreeForMonth($month, $year)
            ->filter(fn ($w) => ! in_array($w->type, $savingsTypes)
                && (! $ownerId || $w->id === $ownerId))
            ->values();

        // Cumulative saldo — only savings/investment wallets, filtered by owner if set
        $walletTreeSavings = $this->walletBalanceService
            ->walletTree()
            ->filter(fn ($w) => in_array($w->type, $savingsTypes)
                && (! $ownerId || $w->id === $ownerId))
            ->values();

        return view('dashboard', compact(
            'summary', 'breakdown', 'recent',
            'walletTreeMonth', 'walletTreeSavings',
            'month', 'year', 'breakdownFilter',
            'rootWallets', 'ownerId'
        ));
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
