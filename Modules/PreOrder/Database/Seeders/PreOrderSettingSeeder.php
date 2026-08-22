<?php

namespace Modules\PreOrder\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\PreOrder\App\Models\PreOrderSetting;

class PreOrderSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PreOrderSetting::truncate();

        PreOrderSetting::create([
            'pre_order_active' => true,
            'pre_order_for_shop' => true,
            'shop_commission' => 10,
            'commission_type' => 'percentage',
            'pre_order_policy' => 'Learn how pre-orders work, including payment terms, estimated delivery dates, cancellations, and order conditions',
            'pre_order_refund_policy' => 'Understand pre-order refund eligibility, cancellation rules, refund methods, and processing timelines'
        ]);
    }
}
