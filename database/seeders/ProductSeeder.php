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
                'image' => 'https://plus.unsplash.com/premium_photo-1678099940967-73fe30680949?q=80&w=880&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
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
                'image' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?q=80&w=1172&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
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
                'image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?q=80&w=1169&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
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
                'image' => 'https://images.unsplash.com/photo-1601036559620-3a83dfdead09?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
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
                'image' => 'https://images.unsplash.com/photo-1580226223521-5412ec88237a?q=80&w=1271&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'category' => 'Accessories',
                'condition' => 'new',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}