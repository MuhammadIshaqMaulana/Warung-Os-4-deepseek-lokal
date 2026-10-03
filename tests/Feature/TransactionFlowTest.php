<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    private function product(User $user, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Teh Botol',
            'category' => 'Minuman',
            'stock' => 10,
            'buy_price' => 3000,
            'sell_price' => 5000,
        ], $overrides));
    }

    public function test_cash_transaction_is_paid_and_reduces_stock(): void
    {
        $user = $this->user();
        $product = $this->product($user);

        $response = $this->actingAs($user)->post('/transactions', [
            'items' => [
                ['id' => $product->id, 'quantity' => 3],
            ],
            'method' => 'cash',
        ]);

        $response->assertRedirect(route('transactions.index'));

        $transaction = Transaction::first();
        $this->assertSame('cash', $transaction->method);
        $this->assertSame('paid', $transaction->status);
        $this->assertEquals(15000, $transaction->total_price);
        $this->assertNotNull($transaction->paid_at);

        $product->refresh();
        $this->assertSame(7, $product->stock);

        $log = StockLog::where('product_id', $product->id)->orderByDesc('id')->first();
        $this->assertSame('out', $log->change_type);
        $this->assertSame(3, $log->quantity);
        $this->assertStringContainsString('Penjualan', (string) $log->note);
    }

    public function test_transaction_with_insufficient_stock_is_rejected(): void
    {
        $user = $this->user();
        $product = $this->product($user, ['stock' => 2]);

        $response = $this->actingAs($user)->post('/transactions', [
            'items' => [
                ['id' => $product->id, 'quantity' => 5],
            ],
            'method' => 'cash',
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertSame(0, Transaction::count());
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_duplicate_items_are_merged_into_one_detail(): void
    {
        $user = $this->user();
        $product = $this->product($user);

        $this->actingAs($user)->post('/transactions', [
            'items' => [
                ['id' => $product->id, 'quantity' => 2],
                ['id' => $product->id, 'quantity' => 3],
            ],
            'method' => 'cash',
        ]);

        $transaction = Transaction::first();
        $this->assertSame(1, $transaction->details()->count());
        $this->assertSame(5, $transaction->details()->first()->quantity);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_qris_transaction_starts_pending_with_expiry(): void
    {
        $user = $this->user();
        $product = $this->product($user);

        $response = $this->actingAs($user)->post('/transactions', [
            'items' => [
                ['id' => $product->id, 'quantity' => 1],
            ],
            'method' => 'qris',
        ]);

        $transaction = Transaction::first();
        $response->assertRedirect(route('transactions.show', $transaction));

        $this->assertSame('pending', $transaction->status);
        $this->assertSame('qris', $transaction->method);
        $this->assertNull($transaction->paid_at);
        $this->assertNotNull($transaction->expires_at);
        $this->assertTrue($transaction->expires_at->isFuture());
        $this->assertStringStartsWith('QRIS-', (string) $transaction->external_id);
        $this->assertSame('pending', $transaction->details()->first()->status);

        // Stok sudah direserve saat transaksi dibuat
        $this->assertSame(9, $product->fresh()->stock);
    }

    public function test_qris_payment_can_be_confirmed_and_is_idempotent(): void
    {
        $user = $this->user();
        $product = $this->product($user);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 2]],
            'method' => 'qris',
        ]);

        $transaction = Transaction::first();

        $this->actingAs($user)->post("/transactions/{$transaction->id}/pay")
            ->assertRedirect(route('transactions.show', $transaction));

        $transaction->refresh();
        $this->assertSame('paid', $transaction->status);
        $this->assertNotNull($transaction->paid_at);
        $this->assertSame('paid', $transaction->details()->first()->status);

        // Pembayaran ganda tidak merusak data
        $this->actingAs($user)->post("/transactions/{$transaction->id}/pay");
        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_pending_qris_can_be_cancelled_and_stock_restored(): void
    {
        $user = $this->user();
        $product = $this->product($user, ['stock' => 6]);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 4]],
            'method' => 'qris',
        ]);

        $transaction = Transaction::first();
        $this->assertSame(2, $product->fresh()->stock);

        $this->actingAs($user)->post("/transactions/{$transaction->id}/cancel")
            ->assertRedirect(route('transactions.index'));

        $transaction->refresh();
        $this->assertSame('failed', $transaction->status);
        $this->assertSame(6, $product->fresh()->stock);

        $log = StockLog::where('product_id', $product->id)->orderByDesc('id')->first();
        $this->assertSame('in', $log->change_type);
        $this->assertStringContainsString('transaksi', (string) $log->note);
    }

    public function test_retry_after_cancel_re_reserves_stock(): void
    {
        $user = $this->user();
        $product = $this->product($user, ['stock' => 6]);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 4]],
            'method' => 'qris',
        ]);

        $transaction = Transaction::first();
        $oldExternalId = $transaction->external_id;

        $this->actingAs($user)->post("/transactions/{$transaction->id}/cancel");
        $this->assertSame(6, $product->fresh()->stock);

        $this->actingAs($user)->post("/transactions/{$transaction->id}/retry")
            ->assertRedirect(route('transactions.show', $transaction));

        $transaction->refresh();
        $this->assertSame('pending', $transaction->status);
        $this->assertNotSame($oldExternalId, $transaction->external_id);
        $this->assertTrue($transaction->expires_at->isFuture());
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_status_endpoint_returns_json_payload(): void
    {
        $user = $this->user();
        $product = $this->product($user);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 1]],
            'method' => 'qris',
        ]);

        $transaction = Transaction::first();

        $response = $this->actingAs($user)->getJson("/transactions/{$transaction->id}/status");

        $response->assertOk()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('method', 'qris')
            ->assertJsonPath('label', 'MENUNGGU BAYAR');

        $this->assertNotNull($response->json('expires_in'));
    }

    public function test_expired_qris_is_failed_and_stock_restored_on_status_check(): void
    {
        $user = $this->user();
        $product = $this->product($user, ['stock' => 6]);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 4]],
            'method' => 'qris',
        ]);

        $transaction = Transaction::first();
        $transaction->update(['expires_at' => now()->subMinute()]);

        $response = $this->actingAs($user)->getJson("/transactions/{$transaction->id}/status");

        $response->assertOk()->assertJsonPath('status', 'failed');
        $this->assertSame(6, $product->fresh()->stock);
    }

    public function test_user_cannot_see_other_users_transaction(): void
    {
        $owner = $this->user();
        $intruder = $this->user();
        $product = $this->product($owner);

        $this->actingAs($owner)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 1]],
            'method' => 'cash',
        ]);

        $transaction = Transaction::first();

        $this->actingAs($intruder)->get("/transactions/{$transaction->id}")->assertForbidden();
        $this->actingAs($intruder)->post("/transactions/{$transaction->id}/pay")->assertForbidden();
    }

    public function test_transaction_list_shows_filters_and_summary(): void
    {
        $user = $this->user();
        $product = $this->product($user);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 1]],
            'method' => 'cash',
        ]);

        $this->actingAs($user)->get('/transactions')
            ->assertOk()
            ->assertSee('Riwayat Transaksi')
            ->assertSee('LUNAS');

        $this->actingAs($user)->get('/transactions?status=pending')
            ->assertOk()
            ->assertSee('Belum ada transaksi');
    }

    public function test_qris_receipt_page_shows_qr_and_countdown(): void
    {
        $user = $this->user();
        $product = $this->product($user);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 2]],
            'method' => 'qris',
        ]);

        $transaction = Transaction::first();

        $this->actingAs($user)->get("/transactions/{$transaction->id}")
            ->assertOk()
            ->assertSee('Pindai kode untuk membayar')
            ->assertSee($transaction->external_id)
            ->assertSee('Bayar Sekarang (Simulasi Gateway)')
            ->assertSee('id="countdown"', false);
    }

    public function test_paid_cash_receipt_page_renders(): void
    {
        $user = $this->user();
        $product = $this->product($user);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 1]],
            'method' => 'cash',
        ]);

        $transaction = Transaction::first();

        $this->actingAs($user)->get("/transactions/{$transaction->id}")
            ->assertOk()
            ->assertSee('LUNAS')
            ->assertSee('TUNAI');
    }
}
