<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\WalletBalanceService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private WalletBalanceService $balanceService) {}

    public function index(Request $request)
    {
        $ownerId = $request->filled('owner_id') ? (int) $request->input('owner_id') : null;

        $ownerWalletIds = null;
        if ($ownerId) {
            $ownerWalletIds = Wallet::where('id', $ownerId)
                ->orWhere('parent_id', $ownerId)
                ->pluck('id')
                ->toArray();
        }

        $query = Transaction::with(['wallet.parent', 'toWallet.parent', 'category'])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('wallet_id')) {
            $query->where('wallet_id', $request->wallet_id);
        } elseif ($ownerWalletIds) {
            $query->whereIn('wallet_id', $ownerWalletIds);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('notes', 'like', "%{$s}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('wallet', fn ($w) => $w->where('name', 'like', "%{$s}%"));
            });
        }

        $transactions = $query->paginate(20)->withQueryString();
        $categories   = Category::orderBy('name')->get();
        $rootWallets  = Wallet::whereNull('parent_id')->orderBy('name')->get();

        $wallets = Wallet::with('parent')
            ->when($ownerWalletIds, fn ($q) => $q->whereIn('id', $ownerWalletIds))
            ->orderBy('name')
            ->get();

        $balances = $this->balanceService->allBalancesWithRollup();

        return view('transactions.index', compact(
            'transactions', 'categories', 'rootWallets', 'wallets', 'ownerId', 'balances'
        ));
    }

    public function create()
    {
        $wallets = Wallet::with('parent')->orderBy('name')->get();
        $categories = Category::orderBy('type')->orderBy('name')->get();
        $balances = $this->balanceService->allBalancesWithRollup();

        return view('transactions.create', compact('wallets', 'categories', 'balances'));
    }

    public function store(StoreTransactionRequest $request)
    {
        $data = $request->validated();

        if ($data['type'] === 'transfer') {
            $data['category_id'] = null;
        } else {
            $data['to_wallet_id'] = null;
        }

        Transaction::create($data);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['wallet', 'toWallet', 'category']);

        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction)
    {
        $wallets = Wallet::with('parent')->orderBy('name')->get();
        $categories = Category::orderBy('type')->orderBy('name')->get();
        $balances = $this->balanceService->allBalancesWithRollup();

        return view('transactions.edit', compact('transaction', 'wallets', 'categories', 'balances'));
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $data = $request->validated();

        if ($data['type'] === 'transfer') {
            $data['category_id'] = null;
        } else {
            $data['to_wallet_id'] = null;
        }

        $transaction->update($data);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
