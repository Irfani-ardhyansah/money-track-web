<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/category-transactions', [DashboardController::class, 'categoryTransactions'])->name('dashboard.category.transactions');

Route::get('wallets/search', [WalletController::class, 'search'])->name('wallets.search');
Route::resource('wallets', WalletController::class)
    ->except(['show']);

Route::get('categories/search', [CategoryController::class, 'search'])->name('categories.search');
Route::resource('categories', CategoryController::class)
    ->except(['show']);

Route::resource('transactions', TransactionController::class);
