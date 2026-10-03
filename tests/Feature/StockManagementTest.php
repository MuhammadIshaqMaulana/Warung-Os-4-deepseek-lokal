<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    private function product(User $user, int $stock = 10): Product
    {
        return Product::create([
            'user_id' => $user->id,
            'name' => 'Gula Pasir',
            'category' => 'Sembako',
            'stock' => $stock,
            'buy_price' => 14000,
            'sell_price' => 16000,
        ]);
    }

    public function test_stock_history_page_is_rendered(): void
    {
        $user = $this->user();
        $this->product($user);

        $this->actingAs($user)->get('/stocks')
            ->assertOk()
            ->assertSee('Riwayat Stok')
            ->assertSee('Gula Pasir');
    }

    public function test_restock_increases_stock_and_logs_change(): void
    {
        $user = $this->user();
        $product = $this->product($user, 4);

        $response = $this->actingAs($user)->post('/stocks/restock', [
            'product_id' => $product->id,
            'quantity' => 12,
            'note' => 'Restok dari supplier',
        ]);

        $response->assertSessionHas('success');
        $this->assertSame(16, $product->fresh()->stock);

        $log = StockLog::where('product_id', $product->id)->orderByDesc('id')->first();
        $this->assertSame('in', $log->change_type);
        $this->assertSame(12, $log->quantity);
        $this->assertSame('Restok dari supplier', $log->note);
    }

    public function test_stock_can_be_reduced(): void
    {
        $user = $this->user();
        $product = $this->product($user, 10);

        $this->actingAs($user)->post('/stocks/restock', [
            'product_id' => $product->id,
            'quantity' => -3,
            'note' => 'Rusak',
        ])->assertSessionHas('success');

        $this->assertSame(7, $product->fresh()->stock);

        $log = StockLog::where('product_id', $product->id)->orderByDesc('id')->first();
        $this->assertSame('out', $log->change_type);
        $this->assertSame(3, $log->quantity);
    }

    public function test_stock_cannot_go_negative(): void
    {
        $user = $this->user();
        $product = $this->product($user, 2);

        $this->actingAs($user)->post('/stocks/restock', [
            'product_id' => $product->id,
            'quantity' => -5,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_zero_quantity_is_invalid(): void
    {
        $user = $this->user();
        $product = $this->product($user);

        $this->actingAs($user)->post('/stocks/restock', [
            'product_id' => $product->id,
            'quantity' => 0,
        ])->assertSessionHasErrors('quantity');
    }

    public function test_user_cannot_restock_other_users_product(): void
    {
        $owner = $this->user();
        $intruder = $this->user();
        $product = $this->product($owner, 5);

        $this->actingAs($intruder)->post('/stocks/restock', [
            'product_id' => $product->id,
            'quantity' => 50,
        ])->assertSessionHasErrors('product_id');

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_stock_log_can_be_filtered_by_type(): void
    {
        $user = $this->user();
        $this->product($user, 6);

        $this->actingAs($user)->post('/stocks/restock', [
            'product_id' => Product::first()->id,
            'quantity' => 4,
        ]);

        $this->actingAs($user)->get('/stocks?type=in')
            ->assertOk()
            ->assertSee('Masuk');

        $this->actingAs($user)->get('/stocks?type=out')
            ->assertOk()
            ->assertSee('Belum ada riwayat stok');
    }
}
