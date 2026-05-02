<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Transaction;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        ActivityLog::create([
            'action'       => 'created',
            'subject_type' => 'transaction',
            'subject_id'   => $transaction->id,
            'description'  => sprintf(
                'Tambah transaksi %s: Rp %s%s',
                $this->typeLabel($transaction->type),
                number_format((float) $transaction->amount, 0, ',', '.'),
                $transaction->notes ? ' — ' . $transaction->notes : ''
            ),
            'meta' => [
                'type'        => $transaction->type,
                'amount'      => (float) $transaction->amount,
                'wallet_id'   => $transaction->wallet_id,
                'to_wallet_id'=> $transaction->to_wallet_id,
                'category_id' => $transaction->category_id,
                'occurred_at' => $transaction->occurred_at?->toDateString(),
                'notes'       => $transaction->notes,
            ],
        ]);
    }

    public function updated(Transaction $transaction): void
    {
        ActivityLog::create([
            'action'       => 'updated',
            'subject_type' => 'transaction',
            'subject_id'   => $transaction->id,
            'description'  => sprintf(
                'Perbarui transaksi #%d %s: Rp %s',
                $transaction->id,
                $this->typeLabel($transaction->type),
                number_format((float) $transaction->amount, 0, ',', '.')
            ),
            'meta' => [
                'type'        => $transaction->type,
                'amount'      => (float) $transaction->amount,
                'wallet_id'   => $transaction->wallet_id,
                'to_wallet_id'=> $transaction->to_wallet_id,
                'category_id' => $transaction->category_id,
                'occurred_at' => $transaction->occurred_at?->toDateString(),
                'notes'       => $transaction->notes,
                'changes'     => $transaction->getChanges(),
            ],
        ]);
    }

    public function deleted(Transaction $transaction): void
    {
        ActivityLog::create([
            'action'       => 'deleted',
            'subject_type' => 'transaction',
            'subject_id'   => $transaction->id,
            'description'  => sprintf(
                'Hapus transaksi #%d %s: Rp %s',
                $transaction->id,
                $this->typeLabel($transaction->type),
                number_format((float) $transaction->amount, 0, ',', '.')
            ),
            'meta' => [
                'type'        => $transaction->type,
                'amount'      => (float) $transaction->amount,
                'wallet_id'   => $transaction->wallet_id,
                'occurred_at' => $transaction->occurred_at?->toDateString(),
            ],
        ]);
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'income'   => 'pemasukan',
            'expense'  => 'pengeluaran',
            'transfer' => 'transfer',
            default    => $type,
        };
    }
}
