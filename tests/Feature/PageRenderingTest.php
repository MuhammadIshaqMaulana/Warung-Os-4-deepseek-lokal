<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_pages_render_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Kopi Hitam',
            'category' => 'Minuman',
            'stock' => 4,
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);

        $pages = [
            '/dashboard',
            '/products',
            '/products/create',
            "/products/{$product->id}/edit",
            '/transactions',
            '/transactions/create',
            '/stocks',
            '/reports',
            '/reports?type=monthly',
            '/profile',
        ];

        foreach ($pages as $page) {
            $this->actingAs($user)->get($page)->assertOk("Halaman {$page} harus bisa dibuka.");
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        foreach (['/dashboard', '/products', '/transactions', '/stocks', '/reports'] as $page) {
            $this->get($page)->assertRedirect(route('login'));
        }
    }

    public function test_cashier_page_lists_existing_product_categories(): void
    {
        $user = User::factory()->create();

        Product::create([
            'user_id' => $user->id,
            'name' => 'Roti Tawar',
            'category' => 'Sembako',
            'stock' => 3,
            'buy_price' => 1000,
            'sell_price' => 1500,
        ]);

        $this->actingAs($user)->get('/transactions/create')
            ->assertOk()
            ->assertSee('Roti Tawar')
            ->assertSee('Sembako');
    }
}
