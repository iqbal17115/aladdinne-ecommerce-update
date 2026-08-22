<?php

namespace App\Console\Commands;

use App\Events\LowStockAlertEvent;
use App\Models\GeneraleSetting;
use App\Models\Product;
use Illuminate\Console\Command;

class LowStockCommand extends Command
{
    private const MAIL_THROTTLE_MICROSECONDS = 1000000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'low-stock-alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send low stock alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $setting = GeneraleSetting::first();

        if ($setting?->low_stock_alert) {
            $products = Product::query()->isActive()->where('quantity', '<=', $setting?->low_stock_alert_quantity)->get();
            $grouped = $products->groupBy('shop_id')->filter(fn ($shopProducts, $shopId) => ! empty($shopId));
            $mailEnabled = (bool) $setting?->low_stock_mail_notification;

            foreach ($grouped->values() as $index => $shopProducts) {
                $productData = $shopProducts->map(fn ($p) => [
                    'name' => $p->name,
                    'quantity' => $p->quantity,
                ])->toArray();

                event(new LowStockAlertEvent($productData, $shopProducts->first()->shop_id));

                if ($mailEnabled && $index < ($grouped->count() - 1)) {
                    usleep(self::MAIL_THROTTLE_MICROSECONDS);
                }
            }
        }
    }
}
