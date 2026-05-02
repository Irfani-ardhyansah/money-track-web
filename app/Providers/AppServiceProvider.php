<?php

namespace App\Providers;

use App\Models\SavingsAdjustment;
use App\Models\Transaction;
use App\Observers\SavingsAdjustmentObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Transaction::observe(TransactionObserver::class);
        SavingsAdjustment::observe(SavingsAdjustmentObserver::class);
    }
}
