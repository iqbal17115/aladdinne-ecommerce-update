<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::truncate();

        $data = [
            [
                'name' => 'Home',
                'ar_name' => 'الرئيسية',
                'url' => '/',
                'title' => 'Home',
                'original_name' => 'Home',
                'original_url' => '/',
                'order' => 1,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Products',
                'ar_name'=> 'المنتجات',
                'url' => '/products',
                'title' => 'Products',
                'original_name' => 'Products',
                'original_url' => '/products',
                'order' => 2,
                'is_active' => true,
                'is_default' => true,
            ],
            // [
            //     'name' => 'Digital Products',
            //     'ar_name'=> 'المنتجات الرقمية',
            //     'url' => '/digital-products',
            //     'title' => 'Digital Products',
            //     'original_name' => 'Digital Products',
            //     'original_url' => '/digital-products',
            //     'order' => 3,
            //     'is_active' => true,
            //     'is_default' => true,
            // ],
            [
                'name' => 'Shops',
                'ar_name'=> 'المتاجر',
                'url' => '/shops',
                'title' => 'Shops',
                'original_name' => 'Shops',
                'original_url' => '/shops',
                'order' => 4,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Brands',
                'ar_name'=> 'ماركة',
                'url' => '/brands',
                'title' => 'Brands',
                'original_name' => 'Brands',
                'original_url' => '/brands',
                'order' => 5,
                'is_active' => true,
                'is_default' => true,
            ],
            // [
            //     'name' => 'Best Deal',
            //     'ar_name'=> 'أفضل العروض',
            //     'url' => '/best-deal',
            //     'title' => 'Best Deal',
            //     'original_name' => 'Best Deal',
            //     'original_url' => '/best-deal',
            //     'order' => 6,
            //     'is_active' => true,
            //     'is_default' => true,
            // ],
            [
                'name' => 'Contact',
                'ar_name'=> 'اتصل بنا',
                'url' => '/contact-us',
                'title' => 'Contact',
                'original_name' => 'Contact',
                'original_url' => '/contact-us',
                'order' => 7,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Blogs',
                'ar_name'=> 'المدونات',
                'url' => '/blogs',
                'title' => 'Blogs',
                'original_name' => 'Blogs',
                'original_url' => '/blogs',
                'order' => 8,
                'is_active' => true,
                'is_default' => true,
            ],
        ];
        foreach ($data as $item) {
            $exists = Menu::where('name', $item['name'])->first();
            if (!$exists) {
                Menu::create($item);
            }
        }
    }
}
