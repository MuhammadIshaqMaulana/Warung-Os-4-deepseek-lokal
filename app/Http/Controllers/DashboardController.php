<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(TransactionService $transactions)
    {
        // Vercel tidak menjalankan scheduler, jadi kedaluwarsa dibersihkan saat dibuka
        $transactions->expireOverdue();

        $userId = Auth::id();
        $today = Carbon::today();
        $threshold = (int) config('qris.low_stock_threshold', 5);

        $todayQuery = fn () => Transaction::where('user_id', $userId)
            ->whereDate('created_at', $today);

        // Omzet hari ini (transaksi lunas)
        $todaySales = $todayQuery()->paid()->sum('total_price');

        // Profit hari ini (harga jual - harga beli) x jumlah
        $todayProfit = TransactionDetail::whereHas('transaction', function ($q) use ($userId, $today) {
            $q->where('user_id', $userId)->whereDate('created_at', $today)->paid();
        })
            ->join('products', 'products.id', '=', 'transaction_details.product_id')
            ->selectRaw('COALESCE(SUM((transaction_details.price - products.buy_price) * transaction_details.quantity), 0) as profit')
            ->value('profit') ?? 0;

        // Menunggu pembayaran QRIS
        $pendingAmount = Transaction::where('user_id', $userId)->pending()->sum('total_price');
        $pendingCount = Transaction::where('user_id', $userId)->pending()->count();

        // Stok menipis & habis
        $lowStockProducts = Product::where('user_id', $userId)
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->get();

        $lowStockCount = $lowStockProducts->count();
        $outOfStockCount = Product::where('user_id', $userId)->where('stock', '<=', 0)->count();

        // Ringkasan transaksi
        $recentTransactions = Transaction::where('user_id', $userId)
            ->with(['details.product'])
            ->latest()
            ->take(5)
            ->get();

        $todayTransactionsCount = $todayQuery()->count();

        $timelineTransactions = $todayQuery()
            ->with(['details.product'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'todaySales',
            'todayProfit',
            'pendingAmount',
            'pendingCount',
            'lowStockCount',
            'outOfStockCount',
            'lowStockProducts',
            'recentTransactions',
            'todayTransactionsCount',
            'timelineTransactions',
            'threshold'
        ));
    }
}
