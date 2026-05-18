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
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'method' => 'required|in:cash,qris',
        ]);

        DB::beginTransaction();
        try {
            $totalPrice = 0;
            $itemsToProcess = [];

            foreach ($validated['items'] as $item) {
                $product = Product::where('id', $item['id'])
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi.");
                }

                $itemTotal = $product->sell_price * $item['quantity'];
                $totalPrice += $itemTotal;

                $itemsToProcess[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->sell_price
                ];
            }

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'total_price' => $totalPrice,
            ]);

            $status = $validated['method'] === 'cash' ? 'paid' : 'pending';
            $paidAt = $validated['method'] === 'cash' ? now() : null;
            $externalId = $validated['method'] === 'qris' ? 'QRIS-' . strtoupper(uniqid()) : null;

            foreach ($itemsToProcess as $proc) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $proc['product']->id,
                    'quantity' => $proc['quantity'],
                    'price' => $proc['price'],
                    'method' => $validated['method'],
                    'status' => $status,
                    'external_id' => $externalId,
                    'paid_at' => $paidAt,
                ]);

                $proc['product']->decrement('stock', $proc['quantity']);
                StockLog::create([
                    'product_id' => $proc['product']->id,
                    'change_type' => 'out',
                    'quantity' => $proc['quantity'],
                ]);
            }

            DB::commit();

            if ($validated['method'] === 'qris') {
                return redirect()->route('transactions.show', $transaction)->with('success', 'Silakan bayar menggunakan QRIS.');
            }

            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
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
