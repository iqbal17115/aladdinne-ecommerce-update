<?php

namespace Modules\PreOrder\Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Local-only demo pre-order products. Creates 3 pre-order products for every
 * shop so the Pre-order Product List (and the storefront pre-order flow) has
 * data to work with. Project-aware: ReadyEcommerce stores the unit as a
 * relation (unit_id) while other builds keep it as a plain string column.
 *
 * Each run clears its own previously-seeded rows first (matched by the
 * PREDEMO- code prefix), so it is safe to run repeatedly.
 */
class PreOrderProductSeeder extends Seeder
{
    private const CODE_PREFIX = 'PREDEMO-';

    private const NAME_POOL = [
        'Pre-order Special Basket', 'Limited Edition Combo', 'Seasonal Fresh Pack',
        'Exclusive Launch Item', 'Premium Reserve Bundle', 'Early Bird Hamper',
        'Signature Gift Box', 'Farm Fresh Crate', 'Deluxe Starter Pack',
    ];

    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $shopIds = Product::withoutGlobalScopes()
            ->whereNotNull('shop_id')
            ->distinct()
            ->pluck('shop_id')
            ->all();

        if (empty($shopIds)) {
            $shopIds = Shop::pluck('id')->all();
        }
        if (empty($shopIds)) {
            $this->command?->warn('PreOrderProductSeeder: no shops found, skipping.');
            return;
        }

        // Remove products from a previous run of this seeder.
        Product::withoutGlobalScopes()
            ->where('code', 'like', self::CODE_PREFIX . '%')
            ->forceDelete();

        $isEcommerce = config('app.project_key') === 'ReadyEcommerce';
        $unitKey     = $isEcommerce ? 'unit_id' : 'unit';
        $unitIds     = $isEcommerce ? Unit::pluck('id')->all() : [];
        $unitStrings = ['kg', 'pcs', 'ltr', 'gm', 'box', 'pack', 'dozen'];

        $categoryIds = Category::withoutGlobalScopes()->pluck('id')->all();

        $seq   = 0;
        $count = 0;

        foreach ($shopIds as $shopId) {
            $perShop = 3;
            for ($i = 0; $i < $perShop; $i++) {
                $seq++;

                $price    = mt_rand(80, 500) + 0.99;
                $discount = mt_rand(0, 1) ? round($price * (mt_rand(80, 95) / 100), 2) : 0;
                $buyPrice = round($price * (mt_rand(55, 75) / 100), 2);
                $isPrepay = (bool) mt_rand(0, 1);

                $unitValue = $isEcommerce
                    ? ($unitIds[array_rand($unitIds)] ?? null)
                    : $unitStrings[array_rand($unitStrings)];

                $name = self::NAME_POOL[array_rand(self::NAME_POOL)] . ' #' . $seq;

                $product = new Product();
                $product->forceFill([
                    'shop_id'                 => $shopId,
                    'name'                    => $name,
                    'slug'                    => Str::slug($name) . '-' . $seq,
                    'code'                    => self::CODE_PREFIX . str_pad((string) $seq, 5, '0', STR_PAD_LEFT),
                    'description'             => 'Demo pre-order product for local testing.',
                    'short_description'       => 'Demo pre-order product.',
                    'price'                   => $price,
                    'discount_price'          => $discount,
                    'buy_price'               => $buyPrice,
                    'quantity'                => mt_rand(20, 120),
                    'min_order_quantity'      => 1,
                    $unitKey                  => $unitValue,
                    'is_active'               => true,
                    'is_new'                  => true,
                    'is_approve'              => true,
                    'is_preorder'             => true,
                    'expected_delivery_date'  => now()->addDays(mt_rand(7, 45))->format('Y-m-d'),
                    'is_available'            => true,
                    'is_refund'               => (bool) mt_rand(0, 1),
                    'is_prepay'               => $isPrepay,
                    'prepay_amount'           => $isPrepay ? round($price * 0.3, 2) : 0,
                    'preorder_quantity_limit' => mt_rand(1, 5),
                    'preorder_notice'         => 'Ships within the expected delivery window.',
                ]);
                $product->save();

                if (! empty($categoryIds)) {
                    $product->categories()->sync([$categoryIds[array_rand($categoryIds)]]);
                }

                $count++;
            }
        }

        $this->command?->info("PreOrderProductSeeder: created {$count} demo pre-order products across " . count($shopIds) . ' shops.');
    }
}
