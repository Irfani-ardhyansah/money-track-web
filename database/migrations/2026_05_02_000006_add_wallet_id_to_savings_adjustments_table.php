<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_adjustments', function (Blueprint $table) {
            $table->foreignId('wallet_id')->nullable()->after('id')->constrained('wallets');
        });
    }

    public function down(): void
    {
        Schema::table('savings_adjustments', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Wallet::class);
            $table->dropColumn('wallet_id');
        });
    }
};
