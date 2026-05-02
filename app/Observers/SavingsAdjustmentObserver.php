<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\SavingsAdjustment;

class SavingsAdjustmentObserver
{
    public function created(SavingsAdjustment $adjustment): void
    {
        $amount     = (float) $adjustment->amount;
        $walletName = $adjustment->wallet?->name ?? 'tanpa dompet';
        ActivityLog::create([
            'action'       => 'created',
            'subject_type' => 'savings_adjustment',
            'subject_id'   => $adjustment->id,
            'description'  => sprintf(
                'Tambah penyesuaian tabungan [%s]: %sRp %s%s',
                $walletName,
                $amount >= 0 ? '+' : '',
                number_format(abs($amount), 0, ',', '.'),
                $adjustment->notes ? ' — ' . $adjustment->notes : ''
            ),
            'meta' => [
                'wallet_id'   => $adjustment->wallet_id,
                'wallet_name' => $walletName,
                'amount'      => $amount,
                'occurred_at' => $adjustment->occurred_at?->toDateString(),
                'notes'       => $adjustment->notes,
            ],
        ]);
    }

    public function updated(SavingsAdjustment $adjustment): void
    {
        $amount     = (float) $adjustment->amount;
        $walletName = $adjustment->wallet?->name ?? 'tanpa dompet';
        ActivityLog::create([
            'action'       => 'updated',
            'subject_type' => 'savings_adjustment',
            'subject_id'   => $adjustment->id,
            'description'  => sprintf(
                'Perbarui penyesuaian tabungan #%d [%s]: %sRp %s',
                $adjustment->id,
                $walletName,
                $amount >= 0 ? '+' : '',
                number_format(abs($amount), 0, ',', '.')
            ),
            'meta' => [
                'wallet_id'   => $adjustment->wallet_id,
                'wallet_name' => $walletName,
                'amount'      => $amount,
                'occurred_at' => $adjustment->occurred_at?->toDateString(),
                'notes'       => $adjustment->notes,
                'changes'     => $adjustment->getChanges(),
            ],
        ]);
    }

    public function deleted(SavingsAdjustment $adjustment): void
    {
        $amount     = (float) $adjustment->amount;
        $walletName = $adjustment->wallet?->name ?? 'tanpa dompet';
        ActivityLog::create([
            'action'       => 'deleted',
            'subject_type' => 'savings_adjustment',
            'subject_id'   => $adjustment->id,
            'description'  => sprintf(
                'Hapus penyesuaian tabungan #%d [%s]: %sRp %s',
                $adjustment->id,
                $walletName,
                $amount >= 0 ? '+' : '',
                number_format(abs($amount), 0, ',', '.')
            ),
            'meta' => [
                'wallet_id'   => $adjustment->wallet_id,
                'wallet_name' => $walletName,
                'amount'      => $amount,
                'occurred_at' => $adjustment->occurred_at?->toDateString(),
            ],
        ]);
    }
}
