<?php

namespace App\Listeners;

use App\Events\LowStockAlertEvent;
use App\Mail\LowStockAlertMail;
use App\Models\GeneraleSetting;
use App\Models\Shop;
use App\Repositories\NotificationRepository;
use App\Services\NotificationServices;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class LowStockAlertListener implements ShouldQueue
{
    use InteractsWithQueue;

    private const RATE_LIMIT_MESSAGE = 'Too many emails per second';

    private const RETRY_DELAYS_SECONDS = [2, 4, 6];

    /**
     * Handle the event.
     */
    public function handle(LowStockAlertEvent $event)
    {
        try {
            $products = $event->products ?? [];
            $shopId = $event->shopId ?? null;

            if (empty($products) || !$shopId) {
                return;
            }

            $shop = Shop::find($shopId);

            // send consolidated mail
            $setting = GeneraleSetting::first();
            if ($setting?->low_stock_mail_notification && $shop && $shop->user && $shop->user->email) {
                $this->sendLowStockMail($shop->user->email, $products);
            }

            // store one consolidated notification
            $productNames = collect($products)->pluck('name')->implode(', ');
            $notification = NotificationRepository::storeByLowStockRequest((object) [
                'title' => 'Low Stock Alert',
                'content' => 'Products low on stock: ' . $productNames,
                'url' => null,
                'icon' => 'bi-x-octagon-fill',
                'type' => 'warning',
                'shop_id' => $shopId,
            ]);

            // send push notification to shop owner devices
            if ($shop && $shop->user) {
                $devices = $shop->user->devices;
                if ($devices && count($devices) > 0) {
                    $deviceKeys = $devices->pluck('key')->toArray();
                    NotificationServices::sendAlertNotification(
                        $notification,
                        $deviceKeys,
                    );
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th->getMessage(), [
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }

    private function sendLowStockMail(string $email, array $products): void
    {
        $delays = array_merge([0], self::RETRY_DELAYS_SECONDS);
        $lastException = null;

        foreach ($delays as $delay) {
            if ($delay > 0) {
                sleep($delay);
            }

            try {
                Mail::to($email)->send(new LowStockAlertMail($products));

                return;
            } catch (\Throwable $th) {
                if (! str_contains($th->getMessage(), self::RATE_LIMIT_MESSAGE)) {
                    throw $th;
                }

                $lastException = $th;
            }
        }

        if ($lastException) {
            throw $lastException;
        }
    }
}
