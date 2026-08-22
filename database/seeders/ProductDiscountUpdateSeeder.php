<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductDiscountUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::query()->get()->each(function ($product) {
            $product->update([
                'discount' => $product->price - $product->discount_price,
                'discount_type' => 1,
            ]);
        });
    }
}
