<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_adjustments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2); // positive = add, negative = subtract
            $table->date('occurred_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_adjustments');
    }
};
