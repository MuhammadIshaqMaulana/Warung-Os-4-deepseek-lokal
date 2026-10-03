<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrisWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    private function pendingTransaction(User $user): Transaction
    {
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Kopi Sachet',
            'category' => 'Minuman',
            'stock' => 10,
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 2]],
            'method' => 'qris',
        ]);

        return Transaction::first();
    }

    private function postWebhook(array $payload, ?string $signature = null)
    {
        $body = json_encode($payload);

        return $this->call('POST', '/webhooks/qris', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_SIGNATURE' => $signature ?? hash_hmac('sha256', $body, (string) config('qris.webhook_secret')),
        ], $body);
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        $user = $this->user();
        $transaction = $this->pendingTransaction($user);

        $this->postWebhook([
            'external_id' => $transaction->external_id,
            'status' => 'paid',
        ], 'signature-salah')->assertStatus(401);

        $this->assertSame('pending', $transaction->fresh()->status);
    }

    public function test_webhook_without_signature_is_rejected(): void
    {
        $user = $this->user();
        $transaction = $this->pendingTransaction($user);

        $this->postWebhook([
            'external_id' => $transaction->external_id,
            'status' => 'paid',
        ], '')->assertStatus(401);

        $this->assertSame('pending', $transaction->fresh()->status);
    }

    public function test_valid_webhook_marks_transaction_paid(): void
    {
        $user = $this->user();
        $transaction = $this->pendingTransaction($user);
        $product = Product::where('user_id', $user->id)->first();

        $this->postWebhook([
            'external_id' => $transaction->external_id,
            'status' => 'paid',
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('duplicate', false)
            ->assertJsonPath('status', 'paid');

        $transaction->refresh();
        $this->assertSame('paid', $transaction->status);
        $this->assertNotNull($transaction->paid_at);
        $this->assertSame('paid', $transaction->details()->first()->status);

        // Stok tidak dikurangi dua kali oleh callback ganda
        $this->assertSame(8, Product::where('user_id', $user->id)->first()->stock);
    }

    public function test_duplicate_webhook_is_idempotent(): void
    {
        $user = $this->user();
        $transaction = $this->pendingTransaction($user);

        $payload = [
            'external_id' => $transaction->external_id,
            'status' => 'paid',
        ];

        $this->postWebhook($payload)->assertOk();
        $second = $this->postWebhook($payload);

        $second->assertOk()
            ->assertJsonPath('duplicate', true)
            ->assertJsonPath('status', 'paid');

        $this->assertSame(1, Transaction::where('status', 'paid')->count());
        $this->assertSame(8, Product::where('user_id', $user->id)->first()->stock);
    }

    public function test_webhook_with_unknown_reference_returns_404(): void
    {
        $this->user();

        $this->postWebhook([
            'external_id' => 'QRIS-TIDAKADA',
            'status' => 'paid',
        ])->assertStatus(404);
    }

    public function test_failed_webhook_cancels_transaction_and_restores_stock(): void
    {
        $user = $this->user();
        $transaction = $this->pendingTransaction($user);
        $product = Product::where('user_id', $user->id)->first();

        $this->assertSame(8, $product->fresh()->stock);

        $this->postWebhook([
            'external_id' => $transaction->external_id,
            'status' => 'failed',
        ])->assertOk()->assertJsonPath('status', 'failed');

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_webhook_payload_without_reference_returns_422(): void
    {
        $this->user();

        $this->postWebhook(['status' => 'paid'])->assertStatus(422);
    }
}
