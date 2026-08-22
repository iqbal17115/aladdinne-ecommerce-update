<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Media;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $brands = Brand::all();

        return [
            'shop_id' => Shop::all()->random()->id,
            'media_id' => Media::factory()->create(),
            'brand_id' => $brands->random()->id,
            'unit_id' => 1,
            'name' => $this->faker->word,
            'name_ar' => 'منتج تجريبي',
            'quantity' => $this->faker->numberBetween(1, 100),
            'short_description' => $this->faker->sentence,
            'short_description_ar' => 'وصف عربي مختصر لعرض المنتج بشكل واضح وسريع.',
            'description' => $this->faker->sentence,
            'description_ar' => 'هذا وصف عربي تجريبي للمنتج يوضح أهم التفاصيل والفوائد بشكل مختصر.',
            'discount_price' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'is_active' => $this->faker->boolean(),
            'is_approve' => true,
        ];
    }
}
