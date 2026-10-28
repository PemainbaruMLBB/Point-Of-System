<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSaleUnit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@kasirku.test'], [
            'name' => 'Administrator', 'password' => Hash::make('password'), 'role' => 'admin', 'activated_at' => now(),
        ]);
        User::updateOrCreate(['email' => 'kasir@kasirku.test'], [
            'name' => 'Kasir Utama', 'password' => Hash::make('password'), 'role' => 'cashier', 'activated_at' => now(),
        ]);
        User::updateOrCreate(['email' => 'pimpinan@kasirku.test'], [
            'name' => 'Pimpinan', 'password' => Hash::make('password'), 'role' => 'pimpinan', 'activated_at' => now(),
        ]);
        $food = Category::firstOrCreate(['slug' => 'makanan'], ['name' => 'Makanan']);
        $drink = Category::firstOrCreate(['slug' => 'minuman'], ['name' => 'Minuman']);
        $rice = Product::updateOrCreate(['sku' => 'SKU-001'], ['category_id' => $food->id, 'name' => 'Nasi Goreng', 'type' => 'product', 'unit' => 'porsi', 'purchase_price' => 10000, 'selling_price' => 18000, 'stock' => 40, 'minimum_stock' => 5]);
        $tea = Product::updateOrCreate(['sku' => 'SKU-002'], ['category_id' => $drink->id, 'name' => 'Es Teh Manis', 'type' => 'product', 'unit' => 'gelas', 'purchase_price' => 2500, 'selling_price' => 6000, 'stock' => 80, 'minimum_stock' => 10]);
        ProductSaleUnit::updateOrCreate(['product_id' => $rice->id, 'name' => 'box'], ['conversion' => 10, 'selling_price' => 165000, 'is_active' => true]);
        ProductSaleUnit::updateOrCreate(['product_id' => $tea->id, 'name' => 'lusin'], ['conversion' => 12, 'selling_price' => 65000, 'is_active' => true]);
    }
}
