<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $shop = User::role('root')->whereHas('shop')->first()?->shop;

        $colors = $shop->colors;
        $sizes = $shop->sizes;
        $categories = $shop->categories;
        $units = $shop->units;

        foreach ($categories as $category) {
            $subCategories = $category->subCategories()->inRandomOrder()->get();
            $categoryProductTarget = rand(10, 12);
            $categoryProductCount = 0;

            foreach ($subCategories as $subCategory) {
                if ($categoryProductCount >= $categoryProductTarget) {
                    break;
                }

                $subCategoryProductCount = rand(4, 5);

                for ($j = 1; $j <= $subCategoryProductCount; $j++) {
                    $productName = fake()->unique()->words(rand(2, 4), true);

                    $product = Product::factory()->create([
                        'name' => Str::title($productName),
                        'name_ar' => 'منتج تجريبي',
                        'slug' => Str::slug($productName),
                        'short_description_ar' => 'وصف عربي مختصر للمنتج يوضح أهم المزايا بسرعة.',
                        'description_ar' => 'هذا وصف عربي تجريبي للمنتج يساعد في اختبار الواجهة وعرض المحتوى متعدد اللغات.',
                        'unit_id' => $units->random()?->id,
                    ]);

                    // for ($k = 0; $k < 4; $k++) {
                    //     $media = Media::factory()->create();
                    //     $product->medias()->attach($media);
                    // }

                    $product->colors()->attach($colors->random(3));
                    $product->sizes()->attach($sizes->random(4));
                    $product->categories()->attach($category->id);
                    $product->subcategories()->attach($subCategory->id);
                }

                $categoryProductCount += $subCategoryProductCount;
            }
        }
    }
}
