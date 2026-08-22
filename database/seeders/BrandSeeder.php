<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Nike', 'name_ar' => 'نايك'],
            ['name' => 'Adidas', 'name_ar' => 'أديداس'],
            ['name' => 'Apple', 'name_ar' => 'أبل'],
            ['name' => 'Samsung', 'name_ar' => 'سامسونج'],
            ['name' => 'Sony', 'name_ar' => 'سوني'],
            ['name' => 'HP', 'name_ar' => 'إتش بي'],
            ['name' => 'Dell', 'name_ar' => 'ديل'],
            ['name' => 'Lenovo', 'name_ar' => 'لينوفو'],
            ['name' => 'Canon', 'name_ar' => 'كانون'],
            ['name' => 'LG', 'name_ar' => 'إل جي'],
            ['name' => 'Microsoft', 'name_ar' => 'مايكروسوفت'],
            ['name' => 'Puma', 'name_ar' => 'بوما'],
            ['name' => 'H&M', 'name_ar' => 'إتش آند إم'],
            ['name' => 'Zara', 'name_ar' => 'زارا'],
            ['name' => 'Gucci', 'name_ar' => 'غوتشي'],
            ['name' => 'Toyota', 'name_ar' => 'تويوتا'],
            ['name' => 'Honda', 'name_ar' => 'هوندا'],
            ['name' => 'BMW', 'name_ar' => 'بي إم دبليو'],
            ['name' => 'Mercedes-Benz', 'name_ar' => 'مرسيدس بنز'],
        ];

        $shop = User::role('root')->whereHas('shop')->first()?->shop;

        foreach ($brands as $brand) {
            Brand::create([
                'name' => $brand['name'],
                'name_ar' => $brand['name_ar'],
                'slug' => Str::slug($brand['name']),
                'shop_id' => $shop->id,
                'media_id' => Media::factory()->create()->id,
                'is_default' => true,
            ]);
        }
    }
}
