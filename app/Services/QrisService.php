<?php

namespace App\Services;

use App\Models\Transaction;

/**
 * Layanan pembayaran QRIS.
 *
 * Gateway saat ini masih placeholder: QR dibuat dari payload statis milik
 * warung, status dibayar bisa dikonfirmasi lewat simulasi (tombol kasir)
 * atau webhook gateway dengan verifikasi signature & proteksi idempotency.
 */
class QrisService
{
    public function __construct(protected TransactionService $transactions) {}

    /**
     * String yang di-encode menjadi QR.
     */
    public function payload(Transaction $transaction): string
    {
        $merchantPayload = (string) config('qris.merchant_payload');

        if ($merchantPayload !== '') {
            return $merchantPayload;
        }

        // Fallback placeholder bila payload statis belum diatur.
        return 'https://pay.warungos.test/'.$transaction->external_id
            .'?amount='.$transaction->total_price;
    }

    /**
     * URL gambar QR (placeholder generator publik).
     */
    public function qrImageUrl(Transaction $transaction, int $size = 240): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/'
            .'?size='.$size.'x'.$size
            .'&margin=8'
            .'&data='.urlencode($this->payload($transaction));
    }

    /**
     * Status terkini untuk polling JSON dari halaman struk.
     *
     * @return array{status: string, label: string, paid_at: ?string, expires_in: ?int, is_expired: bool}
     */
    public function status(Transaction $transaction): array
    {
        if ($transaction->isPending() && $transaction->isExpired()) {
            $this->transactions->expireOverdue();
            $transaction->refresh();
        }

        return [
            'id' => $transaction->id,
            'status' => $transaction->status,
            'label' => $transaction->statusLabel(),
            'method' => $transaction->method,
            'paid_at' => $transaction->paid_at?->toIso8601String(),
            'expires_in' => $transaction->isPending() && $transaction->expires_at
                ? max(0, now()->diffInSeconds($transaction->expires_at, false))
                : null,
            'is_expired' => $transaction->isExpired(),
        ];
    }

    /**
     * Signature webhook: HMAC-SHA256(body, secret).
     */
    public function sign(string $body): string
    {
        return hash_hmac('sha256', $body, (string) config('qris.webhook_secret'));
    }

    public function verifySignature(?string $signature, string $body): bool
    {
        $expected = $this->sign($body);

        if ($signature === null || $signature === '') {
            return false;
        }

        return hash_equals($expected, $signature);
    }
}
