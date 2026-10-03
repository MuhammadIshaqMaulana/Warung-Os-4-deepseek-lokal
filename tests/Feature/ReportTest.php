<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    public function test_daily_report_shows_revenue_and_profit(): void
    {
        $user = $this->user();

        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Nasi Pecel',
            'category' => 'Makanan',
            'stock' => 10,
            'buy_price' => 7000,
            'sell_price' => 10000,
        ]);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 2]],
            'method' => 'cash',
        ]);

        $response = $this->actingAs($user)->get('/reports?type=daily&date='.now()->format('Y-m-d'));

        $response->assertOk()
            ->assertSee('Laporan Keuangan')
            ->assertSee('Rp 20.000')   // omzet
            ->assertSee('Rp 6.000');   // profit (10.000 - 7.000) x 2

        $this->assertSame(1, Transaction::paid()->count());
    }

    public function test_monthly_report_can_be_rendered(): void
    {
        $user = $this->user();

        $this->actingAs($user)->get('/reports?type=monthly&month='.now()->format('Y-m'))
            ->assertOk()
            ->assertSee('Grafik omzet per hari');
    }

    public function test_pending_qris_is_reported_as_waiting_payment(): void
    {
        $user = $this->user();

        Product::create([
            'user_id' => $user->id,
            'name' => 'Es Teh',
            'category' => 'Minuman',
            'stock' => 5,
            'buy_price' => 500,
            'sell_price' => 1000,
        ]);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => Product::first()->id, 'quantity' => 3]],
            'method' => 'qris',
        ]);

        $this->actingAs($user)->get('/reports?type=daily&date='.now()->format('Y-m-d'))
            ->assertOk()
            ->assertSee('Rp 3.000')   // menunggu bayar
            ->assertSee('MENUNGGU BAYAR');
    }

    public function test_dashboard_shows_today_sales(): void
    {
        $user = $this->user();

        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Mie Ayam',
            'category' => 'Makanan',
            'stock' => 8,
            'buy_price' => 8000,
            'sell_price' => 12000,
        ]);

        $this->actingAs($user)->post('/transactions', [
            'items' => [['id' => $product->id, 'quantity' => 1]],
            'method' => 'cash',
        ]);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Omzet hari ini')
            ->assertSee('Rp 12.000');
    }
}
