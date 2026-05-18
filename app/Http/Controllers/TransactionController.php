<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->with('details.product')
            ->latest()
            ->paginate(10);
            
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $products = Product::where('user_id', Auth::id())->where('stock', '>', 0)->get();
        return view('transactions.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'method' => 'required|in:cash,qris',
        ]);

        $product = Product::where('id', $validated['product_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($product->stock < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Stok tidak mencukupi. Stok saat ini: ' . $product->stock]);
        }

        DB::beginTransaction();
        try {
            $totalPrice = $product->sell_price * $validated['quantity'];

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'total_price' => $totalPrice,
            ]);

            $status = $validated['method'] === 'cash' ? 'paid' : 'pending';
            $paidAt = $validated['method'] === 'cash' ? now() : null;
            // Generate mock external_id for QRIS
            $externalId = $validated['method'] === 'qris' ? 'QRIS-' . strtoupper(uniqid()) : null;

            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'price' => $product->sell_price,
                'method' => $validated['method'],
                'status' => $status,
                'external_id' => $externalId,
                'paid_at' => $paidAt,
            ]);

            // Deduct stock if paid (or pending, depending on business logic, usually pending reserves stock)
            $product->decrement('stock', $validated['quantity']);
            StockLog::create([
                'product_id' => $product->id,
                'change_type' => 'out',
                'quantity' => $validated['quantity'],
            ]);

            DB::commit();

            if ($validated['method'] === 'qris') {
                return redirect()->route('transactions.show', $transaction)->with('success', 'Silakan bayar menggunakan QRIS.');
            }

            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memproses transaksi.']);
        }
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $transaction->load('details.product');
        return view('transactions.show', compact('transaction'));
    }

    public function pay(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $detail = $transaction->details()->first();
        if ($detail && $detail->status === 'pending') {
            $detail->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            return redirect()->route('transactions.show', $transaction)->with('success', 'Pembayaran QRIS berhasil dikonfirmasi.');
        }

        return back()->with('error', 'Transaksi tidak valid atau sudah dibayar.');
    }
}
