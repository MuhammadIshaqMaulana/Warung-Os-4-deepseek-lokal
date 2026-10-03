<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Riwayat perubahan stok + daftar stok menipis.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $threshold = (int) config('qris.low_stock_threshold', 5);

        $lowStock = Product::where('user_id', $userId)
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->get();

        $query = StockLog::with('product')
            ->whereHas('product', fn ($q) => $q->where('user_id', $userId))
            ->latest();

        $filters = [
            'type' => $request->query('type'),
            'product_id' => $request->query('product_id'),
        ];

        if (in_array($filters['type'], [StockLog::TYPE_IN, StockLog::TYPE_OUT], true)) {
            $query->where('change_type', $filters['type']);
        } else {
            $filters['type'] = null;
        }

        if ($filters['product_id']) {
            $query->where('product_id', (int) $filters['product_id']);
        }

        $logs = $query->paginate(20)->withQueryString();
        $products = Product::where('user_id', $userId)->orderBy('name')->get(['id', 'name', 'stock']);

        return view('stocks.index', compact('logs', 'lowStock', 'products', 'threshold', 'filters'));
    }

    /**
     * Update stok cepat (masuk / keluar) dari halaman inventaris.
     */
    public function restock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|between:-100000,100000|not_in:0',
            'note' => 'nullable|string|max:255',
        ]);

        $product = Product::where('id', $validated['product_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (! $product) {
            return back()->withErrors(['product_id' => 'Produk tidak ditemukan.']);
        }

        $delta = (int) $validated['quantity'];
        $newStock = $product->stock + $delta;

        if ($newStock < 0) {
            return back()->withErrors([
                'quantity' => "Stok tidak boleh minus. Stok saat ini {$product->stock}.",
            ]);
        }

        DB::transaction(function () use ($product, $delta, $validated) {
            $locked = Product::whereKey($product->id)->lockForUpdate()->first();
            $locked->stock = $locked->stock + $delta;

            if ($locked->stock < 0) {
                throw new \RuntimeException('Stok tidak boleh minus.');
            }

            $locked->save();

            StockLog::create([
                'product_id' => $product->id,
                'change_type' => $delta > 0 ? StockLog::TYPE_IN : StockLog::TYPE_OUT,
                'quantity' => abs($delta),
                'note' => $validated['note'] ?: ($delta > 0 ? 'Penyesuaian stok masuk' : 'Penyesuaian stok keluar'),
            ]);
        });

        return back()->with('success', "Stok {$product->name} diperbarui menjadi {$newStock}.");
    }
}
