<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'seller_id' => 2, // seller_alpha
                'name' => 'Wireless Headphones',
                'description' => 'Bluetooth headphones with noise cancellation and 20-hour battery life.',
                'price' => 599000,
                'stock_quantity' => 25,
                'image' => 'https://via.placeholder.com/400x400?text=Wireless+Headphones',
                'category' => 'Electronics',
                'condition' => 'new',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'seller_id' => 2,
                'name' => 'Smart Watch Series 5',
                'description' => 'Water resistant smartwatch with fitness tracking and heart rate monitor.',
                'price' => 1299000,
                'stock_quantity' => 10,
                'image' => 'https://via.placeholder.com/400x400?text=Smart+Watch',
                'category' => 'Gadgets',
                'condition' => 'new',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'seller_id' => 3, // seller_bravo
                'name' => 'Leather Handbag',
                'description' => 'Premium handcrafted leather handbag for daily use.',
                'price' => 850000,
                'stock_quantity' => 12,
                'image' => 'https://via.placeholder.com/400x400?text=Leather+Bag',
                'category' => 'Fashion',
                'condition' => 'new',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'seller_id' => 3,
                'name' => 'Vintage Denim Jacket',
                'description' => 'Classic unisex denim jacket, perfect for casual wear.',
                'price' => 450000,
                'stock_quantity' => 8,
                'image' => 'https://via.placeholder.com/400x400?text=Denim+Jacket',
                'category' => 'Apparel',
                'condition' => 'used',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'seller_id' => 3,
                'name' => 'Minimalist Watch',
                'description' => 'Sleek minimalist wristwatch with leather strap.',
                'price' => 300000,
                'stock_quantity' => 20,
                'image' => 'https://via.placeholder.com/400x400?text=Minimalist+Watch',
                'category' => 'Accessories',
                'condition' => 'new',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
