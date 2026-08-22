<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    private const CATEGORY_ARABIC = [
        'Electronics' => 'الإلكترونيات',
        'Fashion & Clothing' => 'الأزياء والملابس',
        'Beauty & Personal Care' => 'الجمال والعناية الشخصية',
        'Home & Living' => 'المنزل والمعيشة',
        'Grocery & Essentials' => 'البقالة والاحتياجات الأساسية',
        'Toys & Games' => 'الألعاب والترفيه',
        'Sports & Outdoors' => 'الرياضة والأنشطة الخارجية',
        'Books & Stationery' => 'الكتب والقرطاسية',
        'Automotive' => 'السيارات',
        'Health & Wellness' => 'الصحة والعافية',
        'Jewelry & Accessories' => 'المجوهرات والإكسسوارات',
    ];

    private const SUBCATEGORY_ARABIC = [
        'Mobile Phones' => 'الهواتف المحمولة',
        'Laptops' => 'أجهزة اللابتوب',
        'Headphones' => 'سماعات الرأس',
        'Smart Watches' => 'الساعات الذكية',
        'Cameras' => 'الكاميرات',
        'Accessories' => 'الإكسسوارات',
        'Tablets' => 'الأجهزة اللوحية',
        'Gaming Consoles' => 'أجهزة الألعاب',
        'Televisions' => 'أجهزة التلفاز',
        'Speakers' => 'مكبرات الصوت',
        'Power Banks' => 'بنوك الطاقة',
        'Computer Components' => 'مكونات الكمبيوتر',
        'Networking Devices' => 'أجهزة الشبكات',
        'Printers & Scanners' => 'الطابعات والماسحات الضوئية',
        'Wearable Devices' => 'الأجهزة القابلة للارتداء',
        "Men's Wear" => 'ملابس رجالية',
        "Women's Wear" => 'ملابس نسائية',
        'Kids Fashion' => 'أزياء الأطفال',
        'Footwear' => 'الأحذية',
        'Bags' => 'الحقائب',
        'Watches' => 'الساعات',
        'Ethnic Wear' => 'الملابس التقليدية',
        'Winter Clothing' => 'الملابس الشتوية',
        'Sports Fashion' => 'الأزياء الرياضية',
        'Innerwear' => 'الملابس الداخلية',
        'Plus Size Fashion' => 'الأزياء ذات المقاسات الكبيرة',
        'Travel Bags' => 'حقائب السفر',
        'Jewelry Sets' => 'أطقم المجوهرات',
        'Sunglasses' => 'النظارات الشمسية',
        'Belts & Wallets' => 'الأحزمة والمحافظ',
        'Skincare' => 'العناية بالبشرة',
        'Makeup' => 'مستحضرات التجميل',
        'Hair Care' => 'العناية بالشعر',
        'Fragrances' => 'العطور',
        'Bath & Body' => 'الاستحمام والجسم',
        'Personal Care Tools' => 'أدوات العناية الشخصية',
        'Nail Care' => 'العناية بالأظافر',
        'Men Grooming' => 'العناية الرجالية',
        'Beauty Devices' => 'أجهزة التجميل',
        'Face Masks' => 'أقنعة الوجه',
        'Organic Beauty' => 'الجمال العضوي',
        'Sun Care' => 'العناية من الشمس',
        'Lip Care' => 'العناية بالشفاه',
        'Body Lotions' => 'لوشن الجسم',
        'Salon Essentials' => 'مستلزمات الصالون',
        'Furniture' => 'الأثاث',
        'Home Decor' => 'ديكور المنزل',
        'Kitchen & Dining' => 'المطبخ والطعام',
        'Bedding' => 'المفروشات',
        'Lighting' => 'الإضاءة',
        'Storage & Organization' => 'التخزين والتنظيم',
        'Curtains & Blinds' => 'الستائر والستائر المعدنية',
        'Bathroom Accessories' => 'إكسسوارات الحمام',
        'Home Appliances' => 'الأجهزة المنزلية',
        'Wall Art' => 'فن الجدران',
        'Cleaning Tools' => 'أدوات التنظيف',
        'Flooring' => 'الأرضيات',
        'Garden Decor' => 'ديكور الحديقة',
        'Cookware' => 'أدوات الطهي',
        'Tableware' => 'أدوات المائدة',
        'Beverages' => 'المشروبات',
        'Snacks' => 'الوجبات الخفيفة',
        'Cooking Essentials' => 'أساسيات الطبخ',
        'Dairy & Eggs' => 'الألبان والبيض',
        'Cleaning Supplies' => 'مستلزمات التنظيف',
        'Household Essentials' => 'الاحتياجات المنزلية',
        'Rice & Grains' => 'الأرز والحبوب',
        'Spices & Seasonings' => 'التوابل والمنكهات',
        'Frozen Foods' => 'الأطعمة المجمدة',
        'Fresh Produce' => 'المنتجات الطازجة',
        'Bakery Items' => 'المخبوزات',
        'Canned Foods' => 'الأطعمة المعلبة',
        'Breakfast Foods' => 'أطعمة الإفطار',
        'Pet Supplies' => 'مستلزمات الحيوانات الأليفة',
        'Laundry Care' => 'العناية بالغسيل',
        'Educational Toys' => 'الألعاب التعليمية',
        'Action Figures' => 'مجسمات الحركة',
        'Board Games' => 'ألعاب الطاولة',
        'Puzzles' => 'الألغاز',
        'Remote Control Toys' => 'ألعاب التحكم عن بعد',
        'Outdoor Play' => 'ألعاب خارجية',
        'Dolls & Playsets' => 'الدمى ومجموعات اللعب',
        'Building Blocks' => 'مكعبات البناء',
        'Pretend Play' => 'ألعاب التمثيل',
        'Ride On Toys' => 'ألعاب الركوب',
        'STEM Toys' => 'ألعاب العلوم والتقنية',
        'Baby Toys' => 'ألعاب الأطفال الرضع',
        'Card Games' => 'ألعاب الورق',
        'Toy Vehicles' => 'مركبات الألعاب',
        'Musical Toys' => 'الألعاب الموسيقية',
        'Fitness Equipment' => 'معدات اللياقة',
        'Sportswear' => 'ملابس رياضية',
        'Camping Gear' => 'معدات التخييم',
        'Cycling' => 'ركوب الدراجات',
        'Team Sports' => 'الرياضات الجماعية',
        'Outdoor Accessories' => 'إكسسوارات خارجية',
        'Running Gear' => 'معدات الجري',
        'Yoga Supplies' => 'مستلزمات اليوغا',
        'Hiking Equipment' => 'معدات المشي',
        'Fishing Gear' => 'معدات الصيد',
        'Water Sports' => 'الرياضات المائية',
        'Winter Sports' => 'الرياضات الشتوية',
        'Exercise Machines' => 'أجهزة التمارين',
        'Sports Protection' => 'معدات الحماية الرياضية',
        'Travel Outdoor Gear' => 'معدات السفر الخارجية',
        'Fiction Books' => 'كتب الروايات',
        'Academic Books' => 'الكتب الأكاديمية',
        'Children Books' => 'كتب الأطفال',
        'Office Supplies' => 'مستلزمات المكتب',
        'Notebooks' => 'دفاتر',
        'Art Supplies' => 'مستلزمات الرسم',
        'Religious Books' => 'الكتب الدينية',
        'Comics & Manga' => 'القصص المصورة والمانغا',
        'Exam Preparation' => 'التحضير للامتحانات',
        'Journals & Diaries' => 'المجلات واليوميات',
        'School Supplies' => 'المستلزمات المدرسية',
        'Pens & Writing' => 'الأقلام والكتابة',
        'Craft Materials' => 'مواد الأشغال اليدوية',
        'Business Books' => 'كتب الأعمال',
        'Reference Books' => 'الكتب المرجعية',
        'Car Accessories' => 'إكسسوارات السيارات',
        'Motorbike Accessories' => 'إكسسوارات الدراجات النارية',
        'Engine Oils' => 'زيوت المحركات',
        'Tyres & Wheels' => 'الإطارات والعجلات',
        'Car Care' => 'العناية بالسيارة',
        'Tools & Equipment' => 'الأدوات والمعدات',
        'Interior Accessories' => 'الإكسسوارات الداخلية',
        'Exterior Accessories' => 'الإكسسوارات الخارجية',
        'Helmets & Safety Gear' => 'الخوذ ومعدات السلامة',
        'Battery & Chargers' => 'البطاريات والشواحن',
        'GPS & Navigation' => 'أنظمة الملاحة',
        'Car Audio' => 'صوتيات السيارة',
        'Emergency Kits' => 'حقائب الطوارئ',
        'Lubricants' => 'مواد التشحيم',
        'Seat Covers' => 'أغطية المقاعد',
        'Vitamins & Supplements' => 'الفيتامينات والمكملات',
        'Medical Supplies' => 'المستلزمات الطبية',
        'Wellness Devices' => 'أجهزة العناية الصحية',
        'Personal Hygiene' => 'النظافة الشخصية',
        'Sexual Wellness' => 'العافية الجنسية',
        'Health Monitors' => 'أجهزة مراقبة الصحة',
        'Herbal Supplements' => 'المكملات العشبية',
        'First Aid' => 'الإسعافات الأولية',
        'Mobility Aids' => 'وسائل المساعدة على الحركة',
        'Sleep Support' => 'دعم النوم',
        'Protein Nutrition' => 'التغذية البروتينية',
        'Mental Wellness' => 'العافية النفسية',
        'Oral Care' => 'العناية بالفم',
        'Sanitary Care' => 'العناية الصحية النسائية',
        'Therapy Equipment' => 'معدات العلاج',
        'Necklaces' => 'القلائد',
        'Earrings' => 'الأقراط',
        'Rings' => 'الخواتم',
        'Bracelets' => 'الأساور',
        'Wallets' => 'المحافظ',
        'Anklets' => 'خلخال',
        'Brooches' => 'دبابيس الزينة',
        'Hair Accessories' => 'إكسسوارات الشعر',
        'Handbags' => 'حقائب اليد',
        'Scarves' => 'الأوشحة',
        'Keychains' => 'سلاسل المفاتيح',
        'Cufflinks' => 'أزرار الأكمام',
        'Luxury Accessories' => 'الإكسسوارات الفاخرة',
        'Gift Sets' => 'مجموعات الهدايا',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Electronics' => [
                'Mobile Phones',
                'Laptops',
                'Headphones',
                'Smart Watches',
                'Cameras',
                'Accessories',
                'Tablets',
                'Gaming Consoles',
                'Televisions',
                'Speakers',
                'Power Banks',
                'Computer Components',
                'Networking Devices',
                'Printers & Scanners',
                'Wearable Devices',
            ],
            'Fashion & Clothing' => [
                "Men's Wear",
                "Women's Wear",
                'Kids Fashion',
                'Footwear',
                'Bags',
                'Watches',
                'Ethnic Wear',
                'Winter Clothing',
                'Sports Fashion',
                'Innerwear',
                'Plus Size Fashion',
                'Travel Bags',
                'Jewelry Sets',
                'Sunglasses',
                'Belts & Wallets',
            ],
            'Beauty & Personal Care' => [
                'Skincare',
                'Makeup',
                'Hair Care',
                'Fragrances',
                'Bath & Body',
                'Personal Care Tools',
                'Nail Care',
                'Men Grooming',
                'Beauty Devices',
                'Face Masks',
                'Organic Beauty',
                'Sun Care',
                'Lip Care',
                'Body Lotions',
                'Salon Essentials',
            ],
            'Home & Living' => [
                'Furniture',
                'Home Decor',
                'Kitchen & Dining',
                'Bedding',
                'Lighting',
                'Storage & Organization',
                'Curtains & Blinds',
                'Bathroom Accessories',
                'Home Appliances',
                'Wall Art',
                'Cleaning Tools',
                'Flooring',
                'Garden Decor',
                'Cookware',
                'Tableware',
            ],
            'Grocery & Essentials' => [
                'Beverages',
                'Snacks',
                'Cooking Essentials',
                'Dairy & Eggs',
                'Cleaning Supplies',
                'Household Essentials',
                'Rice & Grains',
                'Spices & Seasonings',
                'Frozen Foods',
                'Fresh Produce',
                'Bakery Items',
                'Canned Foods',
                'Breakfast Foods',
                'Pet Supplies',
                'Laundry Care',
            ],
            'Toys & Games' => [
                'Educational Toys',
                'Action Figures',
                'Board Games',
                'Puzzles',
                'Remote Control Toys',
                'Outdoor Play',
                'Dolls & Playsets',
                'Building Blocks',
                'Pretend Play',
                'Ride On Toys',
                'STEM Toys',
                'Baby Toys',
                'Card Games',
                'Toy Vehicles',
                'Musical Toys',
            ],
            'Sports & Outdoors' => [
                'Fitness Equipment',
                'Sportswear',
                'Camping Gear',
                'Cycling',
                'Team Sports',
                'Outdoor Accessories',
                'Running Gear',
                'Yoga Supplies',
                'Hiking Equipment',
                'Fishing Gear',
                'Water Sports',
                'Winter Sports',
                'Exercise Machines',
                'Sports Protection',
                'Travel Outdoor Gear',
            ],
            'Books & Stationery' => [
                'Fiction Books',
                'Academic Books',
                'Children Books',
                'Office Supplies',
                'Notebooks',
                'Art Supplies',
                'Religious Books',
                'Comics & Manga',
                'Exam Preparation',
                'Journals & Diaries',
                'School Supplies',
                'Pens & Writing',
                'Craft Materials',
                'Business Books',
                'Reference Books',
            ],
            'Automotive' => [
                'Car Accessories',
                'Motorbike Accessories',
                'Engine Oils',
                'Tyres & Wheels',
                'Car Care',
                'Tools & Equipment',
                'Interior Accessories',
                'Exterior Accessories',
                'Helmets & Safety Gear',
                'Battery & Chargers',
                'GPS & Navigation',
                'Car Audio',
                'Emergency Kits',
                'Lubricants',
                'Seat Covers',
            ],
            'Health & Wellness' => [
                'Vitamins & Supplements',
                'Medical Supplies',
                'Wellness Devices',
                'Personal Hygiene',
                'Sexual Wellness',
                'Health Monitors',
                'Herbal Supplements',
                'First Aid',
                'Mobility Aids',
                'Sleep Support',
                'Protein Nutrition',
                'Mental Wellness',
                'Oral Care',
                'Sanitary Care',
                'Therapy Equipment',
            ],
            'Jewelry & Accessories' => [
                'Necklaces',
                'Earrings',
                'Rings',
                'Bracelets',
                'Wallets',
                'Sunglasses',
                'Anklets',
                'Brooches',
                'Hair Accessories',
                'Handbags',
                'Scarves',
                'Keychains',
                'Cufflinks',
                'Luxury Accessories',
                'Gift Sets',
            ]
        ];

        $shop = User::role('root')->whereHas('shop')->first()?->shop;

        $order = 1;

        foreach ($categories as $categoryName => $subCategoryNames) {
            $category = Category::updateOrCreate([
                'name' => $categoryName,
            ], [
                'name_ar' => $this->categoryArabicName($categoryName),
                'slug' => Str::slug($categoryName),
                'description' => $this->categoryDescription($categoryName),
                'description_ar' => $this->categoryDescriptionArabic($categoryName),
                'status' => true,
                'order_by' => $order,
            ]);

            $order++;

            if ($shop) {
                $shop->categories()->syncWithoutDetaching([$category->id]);
            }

            $subCategoryIds = [];

            foreach ($subCategoryNames as $subCategoryName) {
                $subCategory = SubCategory::updateOrCreate([
                    'shop_id' => $shop?->id,
                    'name' => $subCategoryName,
                ], [
                    'name_ar' => $this->subCategoryArabicName($subCategoryName),
                    'slug' => Str::slug($subCategoryName),
                    'short_description' => $this->subCategoryDescription($subCategoryName),
                    'short_description_ar' => $this->subCategoryDescriptionArabic($subCategoryName),
                    'is_active' => true,
                ]);

                $subCategoryIds[] = $subCategory->id;
            }

            $category->subCategories()->syncWithoutDetaching($subCategoryIds);
        }
    }

    private function categoryDescription(string $categoryName): string
    {
        return "Explore premium {$categoryName} products chosen for quality, value, and everyday convenience.";
    }

    private function categoryArabicName(string $categoryName): string
    {
        return self::CATEGORY_ARABIC[$categoryName] ?? $categoryName;
    }

    private function categoryDescriptionArabic(string $categoryName): string
    {
        return "استكشف منتجات {$this->categoryArabicName($categoryName)} المختارة بعناية للجودة والقيمة وسهولة الاستخدام اليومي.";
    }

    private function subCategoryDescription(string $subCategoryName): string
    {
        return "Browse {$subCategoryName} picks designed for reliable use, style, and smart shopping.";
    }

    private function subCategoryArabicName(string $subCategoryName): string
    {
        return self::SUBCATEGORY_ARABIC[$subCategoryName] ?? $subCategoryName;
    }

    private function subCategoryDescriptionArabic(string $subCategoryName): string
    {
        return "تصفح مجموعة {$this->subCategoryArabicName($subCategoryName)} المناسبة للاستخدام العملي والمظهر الأنيق والتسوق الذكي.";
    }
}
