<?php

namespace Modules\PreOrder\Database\Seeders;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Modules\PreOrder\App\Enums\PreOrderStatus;
use Modules\PreOrder\App\Enums\PrePaymentStatus;
use Modules\PreOrder\App\Models\PreOrder;
use Modules\PreOrder\App\Models\PreOrderItem;
use Modules\PreOrder\App\Models\PreOrderSetting;

/**
 * Local-only demo data for the PreOrder module. Creates a spread of pre-orders
 * (mostly Delivered) across every shop that owns products, so the Order List,
 * Commission and Profit Report screens have realistic data to show. Each run
 * clears its own previously-seeded rows first (marked via admin_note), so it is
 * safe to run repeatedly without piling up.
 */
class PreOrderDemoSeeder extends Seeder
{
    private const MARKER = 'demo-seeded';

    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $customers = Customer::pluck('id')->all();
        if (empty($customers)) {
            $this->command?->warn('PreOrderDemoSeeder: no customers found, skipping.');
            return;
        }

        // Remove rows from a previous run of this seeder (cascade drops their items).
        PreOrder::where('admin_note', self::MARKER)->delete();

        $setting     = PreOrderSetting::first();
        $commRate    = (float) ($setting?->shop_commission ?? 10);
        $commType    = $setting?->commission_type ?? 'percentage';
        $isRefundAbleDefault = true;

        // Statuses to spread per shop: mostly Delivered so profit shows up.
        $statuses = [
            PreOrderStatus::DELIVERED->value,
            PreOrderStatus::DELIVERED->value,
            PreOrderStatus::DELIVERED->value,
            PreOrderStatus::DELIVERED->value,
            PreOrderStatus::REQUESTED->value,
            PreOrderStatus::CANCELLED->value,
        ];

        // Pre-orders may ONLY be placed against pre-order products, so pull just
        // the is_preorder products (seeded by PreOrderProductSeeder, which must
        // run first). Bypass the pre-order global scope so they are reachable.
        $products = Product::withoutGlobalScopes()
            ->whereNotNull('shop_id')
            ->where('is_preorder', true)
            ->get()
            ->groupBy('shop_id');

        // Unit is a relation on ReadyEcommerce but a plain string column elsewhere.
        $isEcommerce = config('app.project_key') === 'ReadyEcommerce';

        if ($products->isEmpty()) {
            $this->command?->warn('PreOrderDemoSeeder: no pre-order products found — run PreOrderProductSeeder first. Skipping.');
            return;
        }

        $seq   = (int) (PreOrder::max('id') ?? 0);
        $count = 0;

        foreach ($products as $shopId => $shopProducts) {
            foreach ($statuses as $i => $status) {
                $product = $shopProducts->random();

                $sellPrice = $product->discount_price > 0 ? (float) $product->discount_price : (float) $product->price;
                if ($sellPrice <= 0) {
                    $sellPrice = 100.0; // fallback so demo numbers stay meaningful
                }
                // Buying price: real buy_price if set, else 55-75% of sell price.
                $buyPrice = (float) $product->buy_price;
                if ($buyPrice <= 0) {
                    $buyPrice = round($sellPrice * (mt_rand(55, 75) / 100), 2);
                }

                $qty            = mt_rand(1, 5);
                $deliveryCharge = mt_rand(0, 5) * 10;
                $totalAmount    = round($sellPrice * $qty, 2);
                $payableAmount  = round($totalAmount + $deliveryCharge, 2);

                $isDelivered = $status === PreOrderStatus::DELIVERED->value;

                $commission = 0;
                if ($isDelivered) {
                    $commission = $commType !== 'fixed'
                        ? round($totalAmount * $commRate / 100, 2)
                        : $commRate;
                }

                $createdAt = now()->subDays(mt_rand(0, 29))->subHours(mt_rand(0, 23));

                $seq++;
                $order = PreOrder::create([
                    'shop_id'         => $shopId,
                    'customer_id'     => $customers[array_rand($customers)],
                    'order_code'      => 'PO-' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT),
                    'payable_amount'  => $payableAmount,
                    'total_amount'    => $totalAmount,
                    'paid_amount'     => $isDelivered ? $payableAmount : 0,
                    'delivery_charge' => $deliveryCharge,
                    'payment_status'  => $isDelivered ? PrePaymentStatus::PAID->value : PrePaymentStatus::PENDING->value,
                    'order_status'    => $status,
                    'is_refundable'   => $isRefundAbleDefault,
                    'payment_method'  => 'Cash Payment',
                    'address_id'      => Address::where('customer_id', $customers[array_rand($customers)])->value('id'),
                    'customer_note'   => 'Demo pre-order',
                    'admin_note'      => self::MARKER,
                    'delivered_at'    => $isDelivered ? $createdAt->copy()->addDays(2) : null,
                    'admin_commission' => $commission,
                    'currency_symbol' => '$',
                    'currency_rate'   => 1,
                    'created_at'      => $createdAt,
                    'updated_at'      => $createdAt,
                ]);

                PreOrderItem::create([
                    'pre_order_id' => $order->id,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_price' => (float) $product->price,
                    'buying_price' => $buyPrice,
                    'price'        => $sellPrice,
                    'discount'     => max(0, round((float) $product->price - $sellPrice, 2)),
                    'quantity'     => $qty,
                    'unit'         => $isEcommerce ? ($product->unit?->name ?? null) : ($product->unit ?? null),
                    'created_at'   => $createdAt,
                    'updated_at'   => $createdAt,
                ]);

                $count++;
            }
        }

        $this->command?->info("PreOrderDemoSeeder: created {$count} demo pre-orders across {$products->count()} shops.");
    }
}
