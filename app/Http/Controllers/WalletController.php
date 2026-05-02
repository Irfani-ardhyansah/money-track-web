<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Models\Wallet;
use App\Services\WalletBalanceService;

class WalletController extends Controller
{
    public function __construct(private WalletBalanceService $balanceService) {}

    public function index()
    {
        $walletTree = $this->balanceService->walletTree();

        return view('wallets.index', compact('walletTree'));
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
}
