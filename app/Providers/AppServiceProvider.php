<?php

namespace App\Providers;

use App\Models\SavingsAdjustment;
use App\Models\Transaction;
use App\Observers\SavingsAdjustmentObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (request()->isSecure() || str_contains(request()->getHost(), 'ngrok')) {
            URL::forceScheme('https');
        }
        Transaction::observe(TransactionObserver::class);
        SavingsAdjustment::observe(SavingsAdjustmentObserver::class);
    }
}
