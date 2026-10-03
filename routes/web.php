<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Callback dari payment gateway QRIS (dilindungi signature, tanpa auth session)
Route::post('/webhooks/qris', PaymentWebhookController::class)->name('webhooks.qris');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::post('/stocks/restock', [StockController::class, 'restock'])->name('stocks.restock');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::resource('transactions', TransactionController::class)->except(['edit', 'update', 'destroy']);
    Route::get('/transactions/{transaction}/status', [TransactionController::class, 'status'])->name('transactions.status');
    Route::post('/transactions/{transaction}/pay', [TransactionController::class, 'pay'])->name('transactions.pay');
    Route::post('/transactions/{transaction}/retry', [TransactionController::class, 'retry'])->name('transactions.retry');
    Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
