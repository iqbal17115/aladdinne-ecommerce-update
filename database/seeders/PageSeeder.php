<?php

namespace Database\Seeders;

use App\Models\Page;
use Faker\Factory;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::truncate();

        $faker = Factory::create();

        // Pages
        $pages = [
            [
                'title' => 'Products',
                'title_ar' => 'المنتجات',
                'slug' => 'products',
                'url' => 'products',
                'description' => null,
                'description_ar' => null,
                'is_active' => true,
                'is_default' => true,
                'is_editable' => false,
            ],
            [
                'title' => 'Shops',
                'title_ar' => 'المتاجر',
                'slug' => 'shops',
                'url' => 'shops',
                'description' => null,
                'description_ar' => null,
                'is_active' => true,
                'is_default' => true,
                'is_editable' => false,
            ],
            [
                'title' => 'Most Popular',
                'title_ar' => 'الأكثر شهرة',
                'slug' => 'most-popular',
                'url' => 'most-popular',
                'description' => null,
                'description_ar' => null,
                'is_active' => true,
                'is_default' => true,
                'is_editable' => false,
            ],
            [
                'title' => 'Digital Products',
                'title_ar' => 'المنتجات الرقمية',
                'slug' => 'digital-products',
                'url' => 'digital-products',
                'description' => null,
                'description_ar' => null,
                'is_active' => true,
                'is_default' => true,
                'is_editable' => false,
            ],
            [
                'title' => 'Best Deal',
                'title_ar' => 'أفضل العروض',
                'slug' => 'best-deal',
                'url' => 'best-deal',
                'description' => null,
                'description_ar' => null,
                'is_active' => true,
                'is_default' => true,
                'is_editable' => false,
            ],
            [
                'title' => 'Contact',
                'title_ar' => 'اتصل بنا',
                'slug' => 'contact-us',
                'url' => 'contact-us',
                'description' => null,
                'description_ar' => null,
                'is_active' => true,
                'is_default' => true,
                'is_editable' => false,
            ],
            [
                'title' => 'Blogs',
                'title_ar' => 'المدونات',
                'slug' => 'blogs',
                'url' => 'blogs',
                'description' => null,
                'description_ar' => null,
                'is_active' => true,
                'is_default' => true,
                'is_editable' => false,
            ],
            [
                'title' => 'About Us',
                'title_ar' => 'من نحن',
                'slug' => 'about-us',
                'url' => 'about-us',
                'description' => $faker->randomHtml(4, rand(4, 10)),
                'description_ar' => $this->arabicHtmlBlock(),
                'is_active' => true,
                'is_default' => true,
                'is_editable' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'title_ar' => 'سياسة الخصوصية',
                'slug' => 'privacy-policy',
                'url' => 'privacy-policy',
                'description' => $faker->randomHtml(),
                'description_ar' => $this->arabicHtmlBlock(),
                'is_active' => true,
                'is_default' => true,
                'is_editable' => true,
            ],
            [
                'title' => 'Terms of Service',
                'title_ar' => 'شروط الخدمة',
                'slug' => 'terms-and-conditions',
                'url' => 'terms-and-conditions',
                'description' => $faker->randomHtml(),
                'description_ar' => $this->arabicHtmlBlock(),
                'is_active' => true,
                'is_default' => true,
                'is_editable' => true,
            ],
            [
                'title' => 'Return policy / Refund Policy',
                'title_ar' => 'سياسة الإرجاع والاسترداد',
                'slug' => 'return-and-refund-policy',
                'url' => 'page/return-and-refund-policy',
                'description' => $faker->randomHtml(),
                'description_ar' => $this->arabicHtmlBlock(),
                'is_active' => true,
                'is_default' => false,
                'is_editable' => true,
            ],
            [
                'title' => 'Shipping & Delivery Policy',
                'title_ar' => 'سياسة الشحن والتسليم',
                'slug' => 'shipping-and-delivery-policy',
                'url' => 'page/shipping-and-delivery-policy',
                'description' => $faker->randomHtml(),
                'description_ar' => $this->arabicHtmlBlock(),
                'is_active' => true,
                'is_default' => false,
                'is_editable' => true,
            ],
        ];

        foreach ($pages as $page) {
            $exists = Page::where('slug', $page['slug'])->first();
            if (!$exists) {
                Page::create($page);
            }
        }
    }

    private function arabicHtmlBlock(): string
    {
        return '<h2>محتوى الصفحة</h2><p>هذا نص عربي تجريبي يساعد على اختبار عرض الصفحات الثابتة ودعم تعدد اللغات داخل المتجر.</p><p>يمكن استبدال هذا المحتوى لاحقًا بمحتوى فعلي مناسب لسياسة أو صفحة تعريفية.</p>';
    }
}
