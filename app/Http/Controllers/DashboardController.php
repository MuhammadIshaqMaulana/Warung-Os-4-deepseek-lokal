<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // Total penjualan hari ini (status paid)
        $todaySales = Transaction::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->whereHas('details', function($q) {
                $q->where('status', 'paid');
            })
            ->sum('total_price');

        // Total keuntungan hari ini (harga jual - harga beli * quantity)
        $todayProfit = TransactionDetail::whereHas('transaction', function($q) use ($userId, $today) {
            $q->where('user_id', $userId)->whereDate('created_at', $today);
        })
        ->where('status', 'paid')
        ->with('product')
        ->get()
        ->sum(function ($detail) {
            $profitPerItem = $detail->price - ($detail->product->buy_price ?? 0);
            return $profitPerItem * $detail->quantity;
        });

        // Jumlah stok menipis (stok < 5)
        $lowStockProducts = Product::where('user_id', $userId)
            ->where('stock', '<', 5)
            ->get();
            
        $lowStockCount = $lowStockProducts->count();

        // Ringkasan transaksi terbaru
        $recentTransactions = Transaction::where('user_id', $userId)
            ->with(['details.product'])
            ->latest()
            ->take(5)
            ->get();

        // Jumlah transaksi hari ini
        $todayTransactionsCount = Transaction::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->count();

        // Ringkasan transaksi harian (Timeline)
        $timelineTransactions = Transaction::where('user_id', $userId)
            ->with(['details.product'])
            ->whereDate('created_at', $today)
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'todaySales', 
            'todayProfit', 
            'lowStockCount', 
            'lowStockProducts', 
            'recentTransactions',
            'todayTransactionsCount',
            'timelineTransactions'
        ));
    }
}
