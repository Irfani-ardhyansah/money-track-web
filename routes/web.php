<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('wallets', WalletController::class)
    ->except(['show']);

Route::resource('categories', CategoryController::class)
    ->except(['show']);

Route::resource('transactions', TransactionController::class);
