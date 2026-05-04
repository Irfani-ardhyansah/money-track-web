<?php

namespace Database\Seeders;

use App\Models\SavingsAdjustment;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class SavingsAdjustmentSeeder extends Seeder
{
    public function run(): void
    {
        $walletMap = Wallet::pluck('id', 'name');

        $adjustments = [
            [
                'wallet_id' => $walletMap['Tabungan Darurat'],
                'amount' => 5000000,
                'occurred_at' => '2026-03-01',
                'notes' => 'Saldo awal tabungan darurat',
            ],
            [
                'wallet_id' => $walletMap['Tabungan Liburan'],
                'amount' => 2000000,
                'occurred_at' => '2026-03-01',
                'notes' => 'Saldo awal tabungan liburan',
            ],
            [
                'wallet_id' => $walletMap['Tabungan Liburan'],
                'amount' => 1000000,
                'occurred_at' => '2026-03-25',
                'notes' => 'Setor rutin tabungan liburan Maret',
            ],
            [
                'wallet_id' => $walletMap['Tabungan Liburan'],
                'amount' => 1500000,
                'occurred_at' => '2026-04-25',
                'notes' => 'Setor rutin tabungan liburan April',
            ],
            [
                'wallet_id' => $walletMap['Tabungan Darurat'],
                'amount' => 500000,
                'occurred_at' => '2026-04-25',
                'notes' => 'Tambah dana darurat April',
            ],
            [
                'wallet_id' => $walletMap['Tabungan Liburan'],
                'amount' => -750000,
                'occurred_at' => '2026-04-30',
                'notes' => 'Tarik sebagian untuk akomodasi trip',
            ],
        ];

        foreach ($adjustments as $adjustment) {
            SavingsAdjustment::create($adjustment);
        }
    }
}
