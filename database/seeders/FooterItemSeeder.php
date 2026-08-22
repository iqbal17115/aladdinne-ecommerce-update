<?php

namespace Database\Seeders;

use App\Models\Footer;
use App\Models\FooterItem;
use App\Models\GeneraleSetting;
use Illuminate\Database\Seeder;

class FooterItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FooterItem::query()->delete();

        $generaleSetting = GeneraleSetting::first();

        $footerItems = [
            // Footer 1
            [
                [
                    'id' => 1,
                    'footer_id' => 1,
                    'type' => 'logo',
                    'title' => null,
                    'ar_title' => null,
                    'url' => null,
                    'is_active' => 1,
                    'order' => 0,
                    'is_default' => 1,
                ],
                [
                    'id' => 2,
                    'footer_id' => 1,
                    'type' => 'text',
                    'title' => $generaleSetting?->footer_description ?? 'The ultimate all-in-one solution for your eCommerce business worldwide',
                    'ar_title' => 'الحل الأمثل الشامل لأعمال التجارة الإلكترونية الخاصة بك في جميع أنحاء العالم',
                    'url' => null,
                    'is_active' => 1,
                    'order' => 1,
                    'is_default' => 1,
                ],
                [
                    'id' => 3,
                    'footer_id' => 1,
                    'type' => 'phone',
                    'title' => $generaleSetting?->footer_phone ?? '0123456789',
                    'ar_title' => $generaleSetting?->footer_phone ?? '0123456789',
                    'url' => null,
                    'is_active' => 1,
                    'order' => 2,
                    'is_default' => 1,
                ],
                [
                    'id' => 4,
                    'footer_id' => 1,
                    'type' => 'email',
                    'title' => $generaleSetting?->footer_email ?? 'admin@example.com',
                    'ar_title' => $generaleSetting?->footer_email ?? 'admin@example.com',
                    'url' => null,
                    'is_active' => 1,
                    'order' => 3,
                    'is_default' => 1,
                ],
                [
                    'id' => 5,
                    'footer_id' => 1,
                    'type' => 'social_links',
                    'title' => null,
                    'ar_title' => null,
                    'url' => null,
                    'is_active' => 1,
                    'order' => 4,
                    'is_default' => 1,
                ],
            ],

            // Footer 2 - Shop
            [
                [
                    'id' => 6,
                    'footer_id' => 2,
                    'type' => 'link',
                    'title' => 'All Products',
                    'ar_title' => 'جميع المنتجات',
                    'icon' => 'layout-grid',
                    'url' => '/products',
                    'is_active' => 1,
                    'order' => 0,
                    'is_default' => 0,
                ],
                [
                    'id' => 7,
                    'footer_id' => 2,
                    'type' => 'link',
                    'title' => 'Digital Products',
                    'ar_title' => 'المنتجات الرقمية',
                    'icon' => 'monitor',
                    'url' => '/',
                    'is_active' => 1,
                    'order' => 1,
                    'is_default' => 0,
                ],
                [
                    'id' => 8,
                    'footer_id' => 2,
                    'type' => 'link',
                    'title' => 'Top Rated Products',
                    'ar_title' => 'المنتجات الأعلى تقييماً',
                    'icon' => 'star',
                    'url' => '/most-popular',
                    'is_active' => 1,
                    'order' => 2,
                    'is_default' => 0,
                ],
                [
                    'id' => 9,
                    'footer_id' => 2,
                    'type' => 'link',
                    'title' => 'Best Deals',
                    'ar_title' => 'أفضل العروض',
                    'icon' => 'tag',
                    'url' => '/best-deal',
                    'is_active' => 1,
                    'order' => 3,
                    'is_default' => 0,
                ],
                // [
                //     'id' => 10,
                //     'footer_id' => 2,
                //     'type' => 'link',
                //     'title' => 'New Arrivals',
                //     'ar_title' => 'وصل حديثاً',
                //     'icon' => 'shopping-bag',
                //     'url' => '/new-arrivals',
                //     'is_active' => 1,
                //     'order' => 4,
                //     'is_default' => 0,
                // ],
                // [
                //     'id' => 11,
                //     'footer_id' => 2,
                //     'type' => 'link',
                //     'title' => 'Flash Deals',
                //     'ar_title' => 'عروض فلاش',
                //     'icon' => 'zap',
                //     'url' => '/flash-sale',
                //     'is_active' => 1,
                //     'order' => 5,
                //     'is_default' => 0,
                // ],
                // [
                //     'id' => 12,
                //     'footer_id' => 2,
                //     'type' => 'link',
                //     'title' => 'Gift Cards',
                //     'ar_title' => 'بطاقات الهدايا',
                //     'icon' => 'gift',
                //     'url' => '/gift-cards',
                //     'is_active' => 1,
                //     'order' => 6,
                //     'is_default' => 0,
                // ],
            ],

            // Footer 3 - Customer Service
            [
                [
                    'id' => 13,
                    'footer_id' => 3,
                    'type' => 'link',
                    'title' => 'Help Center',
                    'ar_title' => 'مركز المساعدة',
                    'icon' => 'headset',
                    'url' => '/support',
                    'is_active' => 1,
                    'order' => 0,
                    'is_default' => 0,
                ],
                // [
                //     'id' => 14,
                //     'footer_id' => 3,
                //     'type' => 'link',
                //     'title' => 'Track Your Order',
                //     'ar_title' => 'تتبع طلبك',
                //     'icon' => 'package',
                //     'url' => '/order-history',
                //     'is_active' => 1,
                //     'order' => 1,
                //     'is_default' => 0,
                // ],
                [
                    'id' => 15,
                    'footer_id' => 3,
                    'type' => 'link',
                    'title' => 'Easy Returns',
                    'ar_title' => 'إرجاع سهل',
                    'icon' => 'refresh-cw',
                    'url' => '/return-policy',
                    'is_active' => 1,
                    'order' => 2,
                    'is_default' => 0,
                ],
                [
                    'id' => 16,
                    'footer_id' => 3,
                    'type' => 'link',
                    'title' => 'Shipping Policy',
                    'ar_title' => 'سياسة الشحن',
                    'icon' => 'truck',
                    'url' => '/shipping-policy',
                    'is_active' => 1,
                    'order' => 3,
                    'is_default' => 0,
                ],
                [
                    'id' => 17,
                    'footer_id' => 3,
                    'type' => 'link',
                    'title' => 'Payment Methods',
                    'ar_title' => 'طرق الدفع',
                    'icon' => 'credit-card',
                    'url' => '/',
                    'is_active' => 1,
                    'order' => 4,
                    'is_default' => 0,
                ],
                [
                    'id' => 18,
                    'footer_id' => 3,
                    'type' => 'link',
                    'title' => 'FAQ',
                    'ar_title' => 'الأسئلة الشائعة',
                    'icon' => 'circle-help',
                    'url' => '/',
                    'is_active' => 1,
                    'order' => 5,
                    'is_default' => 0,
                ],
                [
                    'id' => 19,
                    'footer_id' => 3,
                    'type' => 'link',
                    'title' => 'Contact Us',
                    'ar_title' => 'اتصل بنا',
                    'icon' => 'mail',
                    'url' => '/contact-us',
                    'is_active' => 1,
                    'order' => 6,
                    'is_default' => 0,
                ],
            ],

            // Footer 4 - Company
            [
                [
                    'id' => 20,
                    'footer_id' => 4,
                    'type' => 'link',
                    'title' => 'About Us',
                    'ar_title' => 'من نحن',
                    'icon' => 'users',
                    'url' => '/about-us',
                    'is_active' => 1,
                    'order' => 0,
                    'is_default' => 0,
                ],
                // [
                //     'id' => 21,
                //     'footer_id' => 4,
                //     'type' => 'link',
                //     'title' => 'Careers',
                //     'ar_title' => 'وظائف',
                //     'icon' => 'briefcase',
                //     'url' => '/careers',
                //     'is_active' => 1,
                //     'order' => 1,
                //     'is_default' => 0,
                // ],
                [
                    'id' => 22,
                    'footer_id' => 4,
                    'type' => 'link',
                    'title' => 'Blog',
                    'ar_title' => 'المدونة',
                    'icon' => 'file-text',
                    'url' => '/blogs',
                    'is_active' => 1,
                    'order' => 2,
                    'is_default' => 0,
                ],
                [
                    'id' => 23,
                    'footer_id' => 4,
                    'type' => 'link',
                    'title' => 'Terms & Conditions',
                    'ar_title' => 'الشروط والاحكام',
                    'icon' => 'scroll-text',
                    'url' => '/terms-and-conditions',
                    'is_active' => 1,
                    'order' => 3,
                    'is_default' => 0,
                ],
                [
                    'id' => 24,
                    'footer_id' => 4,
                    'type' => 'link',
                    'title' => 'Privacy Policy',
                    'ar_title' => 'سياسة الخصوصية',
                    'icon' => 'shield-check',
                    'url' => '/privacy-policy',
                    'is_active' => 1,
                    'order' => 4,
                    'is_default' => 0,
                ],
                [
                    'id' => 25,
                    'footer_id' => 4,
                    'type' => 'link',
                    'title' => 'Return & Refund Policy',
                    'ar_title' => 'سياسة الإرجاع والاسترداد',
                    'icon' => 'rotate-ccw',
                    'url' => '/return-policy',
                    'is_active' => 1,
                    'order' => 5,
                    'is_default' => 0,
                ],
                [
                    'id' => 26,
                    'footer_id' => 4,
                    'type' => 'link',
                    'title' => 'Become a Seller',
                    'ar_title' => 'صاحب متجر',
                    'icon' => 'store',
                    'url' => '/shop/register',
                    'shop_type' => 'multi',
                    'target' => '_blank',
                    'is_active' => 1,
                    'order' => 6,
                    'is_default' => true,
                ],
            ],

            // Footer 5
            [
                [
                    'id' => 27,
                    'footer_id' => 5,
                    'type' => 'app_store',
                    'title' => null,
                    'ar_title' => null,
                    'url' => null,
                    'is_active' => 1,
                    'order' => 0,
                    'is_default' => true,
                ],
            ],
        ];

        $footers = Footer::all();

        foreach ($footers as $key => $footer) {

            $items = $footerItems[$key];

            foreach ($items as $item) {

                $item['footer_id'] = $footer->id;
                FooterItem::create($item);
            }
        }
    }
}
