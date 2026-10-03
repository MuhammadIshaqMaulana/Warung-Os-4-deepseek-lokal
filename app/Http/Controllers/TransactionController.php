<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\QrisService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactions,
        protected QrisService $qris,
    ) {}

    public function index(Request $request)
    {
        // Bersihkan QRIS yang kedaluwarsa agar status & stok selalu akurat
        $this->transactions->expireOverdue();

        $query = Transaction::where('user_id', Auth::id())
            ->with('details.product')
            ->latest();

        $filters = [
            'status' => $request->query('status'),
            'method' => $request->query('method'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ];

        if (in_array($filters['status'], [Transaction::STATUS_PENDING, Transaction::STATUS_PAID, Transaction::STATUS_FAILED], true)) {
            $query->where('status', $filters['status']);
        } else {
            $filters['status'] = null;
        }

        if (in_array($filters['method'], [Transaction::METHOD_CASH, Transaction::METHOD_QRIS], true)) {
            $query->where('method', $filters['method']);
        } else {
            $filters['method'] = null;
        }

        if ($filters['from']) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if ($filters['to']) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $summary = [
            'count' => (clone $query)->count(),
            'revenue' => (clone $query)->paid()->sum('total_price'),
        ];

        $transactions = $query->paginate(15)->withQueryString();

        return view('transactions.index', compact('transactions', 'summary', 'filters'));
    }

    public function create()
    {
        $products = Product::where('user_id', Auth::id())
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $categories = Product::where('user_id', Auth::id())
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        return view('transactions.create', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1|max:10000',
                'method' => ['required', Rule::in([Transaction::METHOD_CASH, Transaction::METHOD_QRIS])],
            ]);
        } catch (ValidationException $e) {
            Log::warning('Transaksi ditolak: payload tidak valid', [
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
                'items' => $request->input('items'),
                'method' => $request->input('method'),
            ]);

            return redirect()
                ->route('transactions.create')
                ->withErrors($e->errors())
                ->withInput();
        }

        try {
            $transaction = $this->transactions->create(
                Auth::id(),
                $validated['items'],
                $validated['method']
            );
        } catch (\RuntimeException $e) {
            Log::warning('Transaksi gagal disimpan: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'items' => $validated['items'],
                'method' => $validated['method'],
            ]);

            return redirect()
                ->route('transactions.create')
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }

        if ($transaction->method === Transaction::METHOD_QRIS) {
            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', 'Silakan bayar menggunakan QRIS. Pemindaian otomatis terdeteksi oleh sistem.');
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', "Transaksi #{$transaction->id} berhasil disimpan.");
    }

    public function show(Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        if ($transaction->isPending() && $transaction->isExpired()) {
            $this->transactions->expireOverdue();
            $transaction->refresh();
        }

        $transaction->load('details.product');

        return view('transactions.show', [
            'transaction' => $transaction,
            'qrUrl' => $transaction->method === Transaction::METHOD_QRIS && $transaction->isPending()
                ? $this->qris->qrImageUrl($transaction)
                : null,
            'qrPayload' => $transaction->method === Transaction::METHOD_QRIS
                ? $this->qris->payload($transaction)
                : null,
            'statusPayload' => $this->qris->status($transaction),
        ]);
    }

    /**
     * Endpoint polling status pembayaran (JSON).
     */
    public function status(Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        return response()->json($this->qris->status($transaction->fresh()));
    }

    /**
     * Simulasi konfirmasi pembayaran (placeholder gateway).
     */
    public function pay(Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        if ($transaction->isPaid()) {
            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', 'Transaksi sudah lunas.');
        }

        if (! $transaction->isPending()) {
            return redirect()
                ->route('transactions.show', $transaction)
                ->with('error', 'Transaksi tidak dapat dibayar. Silakan buat transaksi baru.');
        }

        $this->transactions->markPaid($transaction, $transaction->external_id, 'simulate');

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Pembayaran QRIS berhasil dikonfirmasi.');
    }

    /**
     * Coba lagi pembayaran QRIS (payload & masa berlaku baru).
     */
    public function retry(Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        if ($transaction->method !== Transaction::METHOD_QRIS || $transaction->isPaid()) {
            return back()->with('error', 'Hanya transaksi QRIS yang belum dibayar yang bisa diulang.');
        }

        try {
            $this->transactions->retry($transaction);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Pembayaran QRIS dibuat ulang. Silakan pindai kode baru.');
    }

    /**
     * Batalkan transaksi QRIS dan kembalikan stok.
     */
    public function cancel(Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        if ($transaction->method !== Transaction::METHOD_QRIS) {
            return back()->with('error', 'Hanya transaksi QRIS yang bisa dibatalkan.');
        }

        if (! $this->transactions->cancel($transaction, 'Dibatalkan kasir')) {
            return back()->with('error', 'Transaksi sudah diproses dan tidak bisa dibatalkan.');
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', "Transaksi #{$transaction->id} dibatalkan dan stok dikembalikan.");
    }

    protected function authorizeTransaction(Transaction $transaction): void
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
