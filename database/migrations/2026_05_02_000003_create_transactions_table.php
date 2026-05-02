<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // income | expense | transfer
            $table->date('occurred_at');
            $table->decimal('amount', 15, 2);
            $table->foreignId('wallet_id')->constrained('wallets');
            $table->foreignId('to_wallet_id')->nullable()->constrained('wallets');
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
