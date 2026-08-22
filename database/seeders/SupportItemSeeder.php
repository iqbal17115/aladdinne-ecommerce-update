<?php

namespace Database\Seeders;

use App\Models\SupportItem;
use Illuminate\Database\Seeder;

class SupportItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'icon' => 'assets/icons/secure-payment.svg',
                'title' => 'Secure Payments',
                'ar_title' => 'مدفوعات آمنة',
                'description' => '100% safe & trusted',
                'ar_description' => 'آمنة وموثوقة 100%',
                'order' => 1,
            ],
            [
                'icon' => 'assets/icons/truck-time.svg',
                'title' => 'Free Delivery',
                'ar_title' => 'توصيل مجاني',
                'description' => 'On orders over 999',
                'ar_description' => 'للطلبات التي تزيد عن 999',
                'order' => 2,
            ],
            [
                'icon' => 'assets/icons/card_support.svg',
                'title' => '100% Authentic',
                'ar_title' => 'أصلي 100%',
                'description' => 'Genuine products only',
                'ar_description' => 'منتجات أصلية فقط',
                'order' => 3,
            ],
            [
                'icon' => 'assets/icons/support.svg',
                'title' => '24/7 Support',
                'ar_title' => 'دعم على مدار الساعة',
                'description' => 'We are always here',
                'ar_description' => 'نحن دائما هنا',
                'order' => 4,
            ],
            [
                'icon' => 'assets/icons/easy-return.svg',
                'title' => 'Easy Returns',
                'ar_title' => 'إرجاع سهل',
                'description' => 'Hassle free returns',
                'ar_description' => 'إرجاع بدون متاعب',
                'order' => 5,
            ],
        ];

        foreach ($items as $item) {
            SupportItem::updateOrCreate(
                ['title' => $item['title']],
                array_merge($item, ['is_active' => true])
            );
        }
    }
}
