<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\QrisService;
use App\Services\TransactionService;
use Illuminate\Http\Request;

/**
 * Endpoint callback (webhook) dari payment gateway QRIS.
 *
 * Keamanan:
 *  - Verifikasi signature HMAC-SHA256 dari body request (X-Signature).
 *  - Idempotent: callback ganda tidak mengubah status dua kali.
 */
class PaymentWebhookController extends Controller
{
    public function __construct(
        protected QrisService $qris,
        protected TransactionService $transactions,
    ) {}

    public function __invoke(Request $request)
    {
        if ((string) config('qris.webhook_secret') === '') {
            return response()->json([
                'ok' => false,
                'message' => 'QRIS_WEBHOOK_SECRET belum dikonfigurasi.',
            ], 503);
        }

        $raw = $request->getContent();
        $signature = $request->header('X-Signature') ?? $request->header('X-Callback-Signature');

        if (! $this->qris->verifySignature($signature, $raw)) {
            return response()->json([
                'ok' => false,
                'message' => 'Signature tidak valid.',
            ], 401);
        }

        $externalId = $request->input('external_id') ?? $request->input('order_id');

        if (! $externalId) {
            return response()->json([
                'ok' => false,
                'message' => 'external_id wajib diisi.',
            ], 422);
        }

        $transaction = Transaction::where('external_id', $externalId)->first();

        if (! $transaction) {
            return response()->json([
                'ok' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        $status = (string) ($request->input('status') ?? 'paid');

        if ($status === 'paid') {
            $firstTime = $this->transactions->markPaid($transaction, $externalId, 'webhook');

            return response()->json([
                'ok' => true,
                'duplicate' => ! $firstTime,
                'status' => $transaction->fresh()->status,
            ]);
        }

        if (in_array($status, ['failed', 'expired', 'cancel'], true)) {
            $cancelled = $this->transactions->cancel($transaction, 'Dibatalkan gateway');

            return response()->json([
                'ok' => true,
                'duplicate' => ! $cancelled,
                'status' => $transaction->fresh()->status,
            ]);
        }

        return response()->json([
            'ok' => true,
            'status' => 'ignored',
            'message' => "Status '{$status}' tidak diproses.",
        ]);
    }
}
