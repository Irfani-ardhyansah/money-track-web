<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavingsAdjustmentRequest;
use App\Http\Requests\UpdateSavingsAdjustmentRequest;
use App\Models\SavingsAdjustment;
use App\Services\SavingsService;
use App\Services\WalletBalanceService;

class SavingsController extends Controller
{
    public function __construct(
        private SavingsService $savingsService,
        private WalletBalanceService $balanceService,
    ) {}

    public function index()
    {
        $grandTotal   = $this->savingsService->grandTotal();
        $totalTracked = $this->savingsService->totalTracked();
        $totalManual  = $this->savingsService->totalManual();
        $walletTree   = $this->savingsService->walletTreeWithAdjustments();

        return view('savings.index', compact(
            'grandTotal',
            'totalTracked',
            'totalManual',
            'walletTree',
        ));
    }

    public function create()
    {
        $balances = $this->balanceService->allBalancesWithRollup();

        return view('savings.create', compact('balances'));
    }

    public function store(StoreSavingsAdjustmentRequest $request)
    {
        $data = $request->validated();

        SavingsAdjustment::create([
            'wallet_id'   => $data['wallet_id'],
            'amount'      => $data['direction'] === 'subtract'
                ? -abs((float) $data['amount'])
                : abs((float) $data['amount']),
            'occurred_at' => $data['occurred_at'],
            'notes'       => $data['notes'] ?? null,
        ]);

        return redirect()->route('savings.index')
            ->with('success', 'Penyesuaian tabungan berhasil ditambahkan.');
    }

    public function edit(SavingsAdjustment $saving)
    {
        $balances = $this->balanceService->allBalancesWithRollup();

        return view('savings.edit', compact('saving', 'balances'));
    }

    public function update(UpdateSavingsAdjustmentRequest $request, SavingsAdjustment $saving)
    {
        $data = $request->validated();

        $saving->update([
            'wallet_id'   => $data['wallet_id'],
            'amount'      => $data['direction'] === 'subtract'
                ? -abs((float) $data['amount'])
                : abs((float) $data['amount']),
            'occurred_at' => $data['occurred_at'],
            'notes'       => $data['notes'] ?? null,
        ]);

        return redirect()->route('savings.index')
            ->with('success', 'Penyesuaian tabungan berhasil diperbarui.');
    }

    public function destroy(SavingsAdjustment $saving)
    {
        $saving->delete();

        return redirect()->route('savings.index')
            ->with('success', 'Penyesuaian tabungan berhasil dihapus.');
    }
}
