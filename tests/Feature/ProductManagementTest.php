<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    public function test_product_can_be_created_with_initial_stock_log(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Beras 5kg',
            'category' => 'Sembako',
            'stock' => 10,
            'buy_price' => 60000,
            'sell_price' => 70000,
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::firstWhere('name', 'Beras 5kg');
        $this->assertNotNull($product);
        $this->assertSame($user->id, $product->user_id);
        $this->assertSame(10, $product->stock);

        $log = StockLog::where('product_id', $product->id)->first();
        $this->assertSame('in', $log->change_type);
        $this->assertSame(10, $log->quantity);
    }

    public function test_product_stock_change_is_logged_on_update(): void
    {
        $user = $this->user();

        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Indomie',
            'category' => 'Makanan',
            'stock' => 5,
            'buy_price' => 2500,
            'sell_price' => 3500,
        ]);

        $this->actingAs($user)->put("/products/{$product->id}", [
            'name' => 'Indomie Goreng',
            'category' => 'Makanan',
            'stock' => 20,
            'buy_price' => 2500,
            'sell_price' => 3500,
        ])->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertSame(20, $product->stock);
        $this->assertSame('Indomie Goreng', $product->name);

        $log = StockLog::where('product_id', $product->id)->latest('id')->first();
        $this->assertSame('in', $log->change_type);
        $this->assertSame(15, $log->quantity);
    }

    public function test_user_cannot_update_other_users_product(): void
    {
        $owner = $this->user();
        $intruder = $this->user();

        $product = Product::create([
            'user_id' => $owner->id,
            'name' => 'Produk Orang',
            'stock' => 1,
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);

        $this->actingAs($intruder)
            ->put("/products/{$product->id}", [
                'name' => 'Hacked',
                'stock' => 999,
                'buy_price' => 1,
                'sell_price' => 1,
            ])
            ->assertForbidden();
    }

    public function test_product_can_be_deleted(): void
    {
        $user = $this->user();

        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Akan Dihapus',
            'stock' => 3,
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);

        $this->actingAs($user)->delete("/products/{$product->id}")
            ->assertRedirect(route('products.index'));

        $this->assertNull(Product::find($product->id));
        $this->assertNotNull(Product::withTrashed()->find($product->id));
    }
}
