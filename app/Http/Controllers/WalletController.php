<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Models\SavingsAdjustment;
use App\Models\Wallet;
use App\Services\WalletBalanceService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private WalletBalanceService $balanceService) {}

    public function index(Request $request)
    {
        $ownerId      = $request->filled('owner_id') ? (int) $request->input('owner_id') : null;
        $parentWallets = Wallet::whereNull('parent_id')->orderBy('name')->get();
        $walletTree   = $this->balanceService->walletTree($ownerId);

        return view('wallets.index', compact('walletTree', 'parentWallets', 'ownerId'));
    }

    public function create()
    {
        $parents = Wallet::whereNull('parent_id')->orderBy('name')->get();

        return view('wallets.create', compact('parents'));
    }

    public function store(StoreWalletRequest $request)
    {
        Wallet::create($request->validated());

        return redirect()->route('wallets.index')
            ->with('success', 'Dompet berhasil ditambahkan.');
    }

    public function edit(Wallet $wallet)
    {
        $parents = Wallet::whereNull('parent_id')
            ->where('id', '!=', $wallet->id)
            ->orderBy('name')
            ->get();

        return view('wallets.edit', compact('wallet', 'parents'));
    }

    public function update(UpdateWalletRequest $request, Wallet $wallet)
    {
        $wallet->update($request->validated());

        return redirect()->route('wallets.index')
            ->with('success', 'Dompet berhasil diperbarui.');
    }

    public function destroy(Wallet $wallet)
    {
        $wallet->delete();

        return redirect()->route('wallets.index')
            ->with('success', 'Dompet berhasil dihapus.');
    }

    public function search(Request $request)
    {
        $q           = $request->input('q', '');
        $filterTypes = $request->input('filter_type')
            ? explode(',', $request->input('filter_type'))
            : [];

        $ownerWalletIds = null;
        if ($request->filled('owner_id')) {
            $ownerId = (int) $request->input('owner_id');
            $ownerWalletIds = Wallet::where(function ($query) use ($ownerId) {
                $query->where('id', $ownerId)->orWhere('parent_id', $ownerId);
            })->pluck('id');
        }

        $wallets = Wallet::with('parent')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhereHas('parent', fn ($p) => $p->where('name', 'like', "%{$q}%"));
            })
            ->when($ownerWalletIds, fn ($query) => $query->whereIn('id', $ownerWalletIds))
            ->when($filterTypes, fn ($query) => $query->whereIn('type', $filterTypes))
            ->orderBy('name')
            ->limit(20)
            ->get();

        $savingsTypes    = ['savings', 'investment'];
        $walletIds       = $wallets->pluck('id');
        $allTimeBalances = $this->balanceService->allBalancesWithRollup();
        $monthlyBalances = $this->balanceService->allMonthlyBalances(now()->month, now()->year);

        $adjustments = SavingsAdjustment::whereIn('wallet_id', $walletIds)
            ->selectRaw('wallet_id, SUM(amount) as total')
            ->groupBy('wallet_id')
            ->pluck('total', 'wallet_id')
            ->map(fn ($v) => (float) $v);

        return response()->json(
            $wallets->map(function ($wallet) use ($savingsTypes, $allTimeBalances, $monthlyBalances, $adjustments) {
                $isSavings = in_array($wallet->type, $savingsTypes)
                    || ($wallet->parent && in_array($wallet->parent->type, $savingsTypes));

                return [
                    'id'      => $wallet->id,
                    'label'   => $wallet->parent
                        ? $wallet->parent->name . ' › ' . $wallet->name
                        : $wallet->name,
                    'balance' => $isSavings
                        ? (float) $allTimeBalances->get($wallet->id, 0) + (float) $adjustments->get($wallet->id, 0)
                        : (float) $monthlyBalances->get($wallet->id, 0),
                ];
            })
        );
    }
}
