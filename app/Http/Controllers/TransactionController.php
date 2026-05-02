<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['wallet', 'toWallet', 'category'])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        $transactions = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'categories'));
    }

    public function create()
    {
        $wallets = Wallet::orderBy('name')->get();
        $categories = Category::orderBy('type')->orderBy('name')->get();

        return view('transactions.create', compact('wallets', 'categories'));
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
        $wallets = Wallet::orderBy('name')->get();
        $categories = Category::orderBy('type')->orderBy('name')->get();

        return view('transactions.edit', compact('transaction', 'wallets', 'categories'));
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
