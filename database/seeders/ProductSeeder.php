<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::where('email', 'a@a.com')->first();
        
        if (!$user) return;

        $products = [
            ['name' => 'Beras Pandan Wangi 5kg', 'category' => 'Sembako', 'buy_price' => 65000, 'sell_price' => 72000, 'stock' => 10],
            ['name' => 'Minyak Goreng 1L', 'category' => 'Sembako', 'buy_price' => 14000, 'sell_price' => 16500, 'stock' => 25],
            ['name' => 'Gula Pasir 1kg', 'category' => 'Sembako', 'buy_price' => 13500, 'sell_price' => 15000, 'stock' => 15],
            ['name' => 'Telur Ayam 1kg', 'category' => 'Sembako', 'buy_price' => 24000, 'sell_price' => 27000, 'stock' => 3],
            ['name' => 'Indomie Goreng', 'category' => 'Mie Instan', 'buy_price' => 2800, 'sell_price' => 3500, 'stock' => 40],
            ['name' => 'Kopi Sachet Kapal Api', 'category' => 'Minuman', 'buy_price' => 1200, 'sell_price' => 1500, 'stock' => 2],
            ['name' => 'Susu Kental Manis 1L', 'category' => 'Susu', 'buy_price' => 18000, 'sell_price' => 21000, 'stock' => 8],
            ['name' => 'Sabun Cuci Piring 800ml', 'category' => 'Pembersih', 'buy_price' => 12000, 'sell_price' => 14500, 'stock' => 12],
            ['name' => 'Shampoo Sachet 12pcs', 'category' => 'Perawatan Tubuh', 'buy_price' => 9000, 'sell_price' => 11000, 'stock' => 6],
            ['name' => 'Pasta Gigi 120g', 'category' => 'Perawatan Tubuh', 'buy_price' => 8500, 'sell_price' => 10500, 'stock' => 20],
            ['name' => 'Teh Celup Kotak', 'category' => 'Minuman', 'buy_price' => 5000, 'sell_price' => 6500, 'stock' => 30],
            ['name' => 'Garam Dapur 250g', 'category' => 'Bumbu', 'buy_price' => 2000, 'sell_price' => 3000, 'stock' => 50],
            ['name' => 'Kecap Manis 225ml', 'category' => 'Bumbu', 'buy_price' => 7500, 'sell_price' => 9000, 'stock' => 4],
            ['name' => 'Saus Sambal 135ml', 'category' => 'Bumbu', 'buy_price' => 6000, 'sell_price' => 7500, 'stock' => 18],
            ['name' => 'Tepung Terigu 1kg', 'category' => 'Sembako', 'buy_price' => 11000, 'sell_price' => 13000, 'stock' => 22],
            ['name' => 'Biskuit Roma Kelapa', 'category' => 'Camilan', 'buy_price' => 8000, 'sell_price' => 9500, 'stock' => 14],
            ['name' => 'Ciki Taro Net', 'category' => 'Camilan', 'buy_price' => 1500, 'sell_price' => 2500, 'stock' => 1],
            ['name' => 'Air Mineral 600ml', 'category' => 'Minuman', 'buy_price' => 2500, 'sell_price' => 3500, 'stock' => 48],
            ['name' => 'Minuman Isotonik 500ml', 'category' => 'Minuman', 'buy_price' => 5500, 'sell_price' => 7000, 'stock' => 12],
            ['name' => 'Roti Tawar Kasur', 'category' => 'Roti', 'buy_price' => 12000, 'sell_price' => 15000, 'stock' => 5],
            ['name' => 'Margarin 200g', 'category' => 'Bahan Kue', 'buy_price' => 6500, 'sell_price' => 8000, 'stock' => 10],
            ['name' => 'Baterai AA 4pcs', 'category' => 'Lainnya', 'buy_price' => 15000, 'sell_price' => 18000, 'stock' => 7],
            ['name' => 'Tisu Wajah 250 sheets', 'category' => 'Lainnya', 'buy_price' => 10000, 'sell_price' => 12500, 'stock' => 15],
            ['name' => 'Obat Nyamuk Bakar', 'category' => 'Lainnya', 'buy_price' => 4000, 'sell_price' => 5500, 'stock' => 20],
            ['name' => 'Korek Api Gas', 'category' => 'Lainnya', 'buy_price' => 1500, 'sell_price' => 2500, 'stock' => 35],
        ];

        foreach ($products as $p) {
            $product = \App\Models\Product::updateOrCreate(
                ['user_id' => $user->id, 'name' => $p['name']],
                $p
            );

            // Add initial stock log
            \App\Models\StockLog::create([
                'product_id' => $product->id,
                'change_type' => 'in',
                'quantity' => $p['stock'],
            ]);
        }
    }
}
