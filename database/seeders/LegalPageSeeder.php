<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Faker\Factory;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        // Legal Pages
        $legalPages = [
            [
                'title' => 'Privacy Policy',
                'title_ar' => 'سياسة الخصوصية',
                'slug' => 'privacy-policy',
                'description' => $faker->randomHtml(),
                'description_ar' => $this->arabicHtmlBlock(),
            ],
            [
                'title' => 'Terms of Service',
                'title_ar' => 'شروط الخدمة',
                'slug' => 'terms-and-conditions',
                'description' => $faker->randomHtml(),
                'description_ar' => $this->arabicHtmlBlock(),
            ],
            [
                'title' => 'Return policy / Refund Policy',
                'title_ar' => 'سياسة الإرجاع والاسترداد',
                'slug' => 'return-and-refund-policy',
                'description' => $faker->randomHtml(),
                'description_ar' => $this->arabicHtmlBlock(),
            ],
            [
                'title' => 'Shipping & Delivery Policy',
                'title_ar' => 'سياسة الشحن والتسليم',
                'slug' => 'shipping-and-delivery-policy',
                'description' => $faker->randomHtml(),
                'description_ar' => $this->arabicHtmlBlock(),
            ],
            [
                'title' => 'About Us',
                'title_ar' => 'من نحن',
                'slug' => 'about-us',
                'description' => $faker->randomHtml(4, rand(4, 10)),
                'description_ar' => $this->arabicHtmlBlock(),
            ],
        ];

        foreach ($legalPages as $legalPage) {
            LegalPage::create($legalPage);
        }
    }

    private function arabicHtmlBlock(): string
    {
        return '<h2>محتوى قانوني</h2><p>هذا محتوى عربي تجريبي لعرض الصفحات القانونية مع دعم اللغة العربية في بيانات البذور.</p><p>يمكن تحديث هذا النص لاحقًا بالمحتوى الرسمي المطلوب.</p>';
    }
}
