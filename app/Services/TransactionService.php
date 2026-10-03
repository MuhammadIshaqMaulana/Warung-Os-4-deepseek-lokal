<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLog;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Simpan transaksi penjualan baru.
     *
     * Stok dikurangi saat transaksi dibuat (reserve), dan dikembalikan
     * bila pembayaran QRIS dibatalkan / kedaluwarsa.
     *
     * @param  array<int, array{id: int, quantity: int}>  $items
     */
    public function create(int $userId, array $items, string $method): Transaction
    {
        $items = $this->mergeItems($items);

        if ($items === []) {
            throw new \RuntimeException('Keranjang kosong atau jumlah produk tidak valid. Pilih produk terlebih dahulu.');
        }

        return DB::transaction(function () use ($userId, $items, $method) {
            $totalPrice = 0;
            $process = [];

            $products = Product::where('user_id', $userId)
                ->whereIn('id', array_column($items, 'id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get((int) $item['id']);

                if (! $product) {
                    throw new \RuntimeException('Produk tidak ditemukan.');
                }

                $quantity = (int) $item['quantity'];

                if ($quantity < 1) {
                    throw new \RuntimeException("Jumlah {$product->name} tidak valid.");
                }

                if ($product->stock < $quantity) {
                    throw new \RuntimeException("Stok {$product->name} tidak mencukupi (tersisa {$product->stock}).");
                }

                $totalPrice += (float) $product->sell_price * $quantity;
                $process[] = ['product' => $product, 'quantity' => $quantity];
            }

            $isQris = $method === Transaction::METHOD_QRIS;

            $transaction = Transaction::create([
                'user_id' => $userId,
                'total_price' => $totalPrice,
                'method' => $method,
                'status' => $isQris ? Transaction::STATUS_PENDING : Transaction::STATUS_PAID,
                'external_id' => $isQris ? $this->generateExternalId() : null,
                'paid_at' => $isQris ? null : now(),
                'expires_at' => $isQris ? now()->addMinutes((int) config('qris.expiry_minutes', 15)) : null,
            ]);

            foreach ($process as $item) {
                /** @var Product $product */
                $product = $item['product'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->sell_price,
                    'method' => $method,
                    'status' => $transaction->status,
                    'external_id' => $transaction->external_id,
                    'paid_at' => $transaction->paid_at,
                ]);

                $product->decrement('stock', $item['quantity']);

                StockLog::create([
                    'product_id' => $product->id,
                    'change_type' => StockLog::TYPE_OUT,
                    'quantity' => $item['quantity'],
                    'note' => "Penjualan #{$transaction->id}",
                ]);
            }

            return $transaction->fresh('details.product');
        });
    }

    /**
     * Tandai transaksi sebagai lunas. Idempotent: aman dipanggil
     * berkali-kali dari webhook (double callback) tanpa efek ganda.
     */
    public function markPaid(Transaction $transaction, ?string $externalId = null, string $source = 'manual'): bool
    {
        $updated = Transaction::whereKey($transaction->id)
            ->where('status', Transaction::STATUS_PENDING)
            ->update([
                'status' => Transaction::STATUS_PAID,
                'paid_at' => now(),
                'external_id' => $externalId ?: $transaction->external_id,
            ]);

        if ($updated === 0) {
            return false;
        }

        TransactionDetail::where('transaction_id', $transaction->id)
            ->update([
                'status' => Transaction::STATUS_PAID,
                'paid_at' => now(),
            ]);

        $transaction->refresh();

        return true;
    }

    /**
     * Batalkan transaksi QRIS yang belum dibayar dan kembalikan stok.
     */
    public function cancel(Transaction $transaction, string $reason = 'Pembatalan'): bool
    {
        return DB::transaction(function () use ($transaction, $reason) {
            $updated = Transaction::whereKey($transaction->id)
                ->where('status', Transaction::STATUS_PENDING)
                ->update(['status' => Transaction::STATUS_FAILED]);

            if ($updated === 0) {
                return false;
            }

            TransactionDetail::where('transaction_id', $transaction->id)
                ->update(['status' => Transaction::STATUS_FAILED]);

            $this->restoreStock($transaction, $reason);

            $transaction->refresh();

            return true;
        });
    }

    /**
     * Perpanjang / regenerasi pembayaran QRIS setelah gagal atau kedaluwarsa.
     * Jika sebelumnya gagal (stok sudah dikembalikan), stok direserve ulang.
     */
    public function retry(Transaction $transaction): Transaction
    {
        DB::transaction(function () use ($transaction) {
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->first();

            if (! $locked || ! in_array($locked->status, [Transaction::STATUS_PENDING, Transaction::STATUS_FAILED], true)) {
                throw new \RuntimeException('Transaksi ini tidak dapat diulang pembayarannya.');
            }

            $needsRestock = $locked->status === Transaction::STATUS_FAILED;

            $locked->update([
                'status' => Transaction::STATUS_PENDING,
                'external_id' => $this->generateExternalId(),
                'expires_at' => now()->addMinutes((int) config('qris.expiry_minutes', 15)),
                'paid_at' => null,
            ]);

            TransactionDetail::where('transaction_id', $transaction->id)
                ->update([
                    'status' => Transaction::STATUS_PENDING,
                    'paid_at' => null,
                ]);

            if ($needsRestock) {
                $this->reserveStock($transaction);
            }
        });

        return $transaction->fresh('details.product');
    }

    /**
     * Kurangi stok kembali untuk transaksi yang diulang dari status gagal.
     */
    protected function reserveStock(Transaction $transaction): void
    {
        $products = Product::whereIn('id', $transaction->details->pluck('product_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($transaction->details as $detail) {
            $product = $products->get($detail->product_id);

            if (! $product || $product->stock < $detail->quantity) {
                throw new \RuntimeException(
                    'Stok '.($product->name ?? 'produk').' tidak mencukupi untuk mengulang pembayaran. Buat transaksi baru.'
                );
            }

            $product->decrement('stock', $detail->quantity);

            StockLog::create([
                'product_id' => $product->id,
                'change_type' => StockLog::TYPE_OUT,
                'quantity' => $detail->quantity,
                'note' => "Pengulangan pembayaran transaksi #{$transaction->id}",
            ]);
        }
    }

    /**
     * Tandai transaksi QRIS kedaluwarsa dan kembalikan stoknya.
     * Dipanggil dari scheduler maupun secara lazy saat status dicek.
     */
    public function expireOverdue(): int
    {
        $overdue = Transaction::pending()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($overdue as $transaction) {
            if ($this->cancel($transaction, 'Pembayaran kedaluwarsa')) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Kembalikan stok untuk detail transaksi (pembatalan / kedaluwarsa).
     */
    protected function restoreStock(Transaction $transaction, string $reason): void
    {
        foreach ($transaction->details as $detail) {
            if (! $detail->product_id) {
                continue;
            }

            Product::whereKey($detail->product_id)->increment('stock', $detail->quantity);

            StockLog::create([
                'product_id' => $detail->product_id,
                'change_type' => StockLog::TYPE_IN,
                'quantity' => $detail->quantity,
                'note' => "{$reason} transaksi #{$transaction->id}",
            ]);
        }
    }

    /**
     * Gabungkan item dengan produk yang sama agar tidak dobel.
     *
     * @param  array<int, array{id: int, quantity: int}>  $items
     * @return array<int, array{id: int, quantity: int}>
     */
    protected function mergeItems(array $items): array
    {
        $merged = [];

        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($id < 1 || $quantity < 1) {
                continue;
            }

            if (! isset($merged[$id])) {
                $merged[$id] = ['id' => $id, 'quantity' => 0];
            }

            $merged[$id]['quantity'] += $quantity;
        }

        return array_values($merged);
    }

    protected function generateExternalId(): string
    {
        return 'QRIS-'.strtoupper(bin2hex(random_bytes(8)));
    }
}
