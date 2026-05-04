<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        // Parent wallets
        $bank = Wallet::create(['name' => 'Bank', 'type' => 'bank', 'parent_id' => null]);
        $eWallet = Wallet::create(['name' => 'E-Wallet', 'type' => 'e-wallet', 'parent_id' => null]);

        // Children of "Bank"
        Wallet::create(['name' => 'BCA', 'type' => 'bank', 'parent_id' => $bank->id]);
        Wallet::create(['name' => 'Mandiri', 'type' => 'bank', 'parent_id' => $bank->id]);

        // Children of "E-Wallet"
        Wallet::create(['name' => 'GoPay', 'type' => 'e-wallet', 'parent_id' => $eWallet->id]);
        Wallet::create(['name' => 'OVO', 'type' => 'e-wallet', 'parent_id' => $eWallet->id]);

        // Standalone wallets
        Wallet::create(['name' => 'Dompet Tunai', 'type' => 'cash', 'parent_id' => null]);
        Wallet::create(['name' => 'Tabungan Liburan', 'type' => 'savings', 'parent_id' => null]);
        Wallet::create(['name' => 'Tabungan Darurat', 'type' => 'savings', 'parent_id' => null]);
    }
}
