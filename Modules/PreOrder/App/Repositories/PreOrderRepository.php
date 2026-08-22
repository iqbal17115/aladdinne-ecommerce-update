<?php

namespace Modules\PreOrder\App\Repositories;


use App\Repositories\Repository;
use App\Enums\PaymentMethod;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Product;
use App\Repositories\DriverRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\WalletRepository;
use App\Services\NotificationServices;
use Illuminate\Support\Facades\DB;
use Modules\PreOrder\App\Enums\PreOrderStatus;
use Modules\PreOrder\App\Enums\PrePaymentStatus;
use Modules\PreOrder\App\Enums\RefundStatus;
use Modules\PreOrder\App\Models\PreOrder;
use Modules\PreOrder\App\Models\PreOrderItem;
use Modules\PreOrder\App\Models\PreOrderSetting;

class PreOrderRepository extends Repository
{
    public static function model()
    {
        return PreOrder::class;
    }
    public static function storeByRequestFrom($request, Product $product, $paymentMethod)
    {
        return DB::transaction(function () use ($request, $product, $paymentMethod) {
            $payment = Payment::create([
                'amount' => 0,
                'payment_method' => $paymentMethod->value,
                'payment_from' => 'preOrder',
            ]);
            $isPrePay = $product->is_prepay ?? false;
            $customer = auth()->user()->customer;
            $currency = Currency::find($request->currencyData['id'] ?? null);
            $getRequestAmount = self::getTotalAmounts($request, $product);
            $price = $product->discount_price > 0 ? $product->discount_price : $product->price;
            $order = self::create([
                'shop_id' => $product->shop_id,
                'order_code' => 'PO-' . str_pad(self::query()->max('id') + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'delivery_charge' => $getRequestAmount['deliveryCharge'],
                'payable_amount' => $getRequestAmount['payableAmount'],
                'total_amount' => $getRequestAmount['totalAmount'],
                'paid_amount'  => 0,
                'payment_method' => $paymentMethod->value,
                'is_refundable' => $product->is_refund ? true : false,
                'order_status' => PreOrderStatus::REQUESTED->value,
                'address_id' => $request->address_id,
                'instruction' => $request->note,
                'payment_status' => PrePaymentStatus::PENDING->value,
                'currency_symbol' => $currency->symbol ?? '$',
                'currency_rate' => $currency->rate ?? 1
            ]);
            PreOrderItem::create([
                'pre_order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_price' => $product->price,
                'quantity' => $request->quantity,
                'unit' => config('app.project_key') === 'ReadyEcommerce' ? $product->unit?->name : $product->unit,
                'price' => $price,
                'discount' => $product->price - $product->discount_price,
                'buying_price' => $product->buyingPrice() ?? 0,
            ]);
            $payment->preOrders()->attach($order->id);
            $payment->update([
                'amount' => $isPrePay ? $getRequestAmount['prePayableAmount'] : $getRequestAmount['payableAmount'],
            ]);
            return $payment;
        });
    }
    public static function getTotalAmounts($request, Product $product): array
    {
        $deliveryCharge = getPreOrderDeliveryCharge($product, $request);
        $price = $product->discount_price > 0 ? $product->discount_price : $product->price;
        $totalAmount = ($price * $request->quantity);
        $prePay = ($product->prepay_amount * $request->quantity);
        // calculate payable amount
        $payableAmount = ($totalAmount + $deliveryCharge);
        $prePayAmount = ($prePay + $deliveryCharge);
        $prePayableAmount = $product->is_prepay ? $prePayAmount : 0;
        // return array
        return [
            'totalAmount' => $totalAmount,
            'payableAmount' => $payableAmount,
            'prePayableAmount' => $prePayableAmount,
            'deliveryCharge' => $deliveryCharge,
        ];
    }

    /**
     * PreOrder status update from rider main controller
     */
    public static function PreOrderStatusUpdateFromRider(PreOrder $order, $driverOrder, $orderStatus)
    {
        DB::transaction(function () use ($order, $driverOrder, $orderStatus) {

            if ($orderStatus == PreOrderStatus::IN_SHIPPING->value) {
                $driverOrder->update(['is_accept' => true]);
            }
            $order->update([
                'order_status' => ($orderStatus == 'Delivered') ? PreOrderStatus::DELIVERED->value : $orderStatus,
            ]);
            if ($orderStatus == PreOrderStatus::PICKUP->value) {
                $order->update([
                    'pick_date' => now(),
                    'order_status' => PreOrderStatus::ON_THE_WAY->value,
                ]);
            }
            $paymentMethod = $order->payment_method->value;
            $isDelivery = false;
            if ($paymentMethod != PaymentMethod::CASH->value && $orderStatus == PreOrderStatus::DELIVERED->value) {
                $isDelivery = true;
            }
            if (($orderStatus == 'Delivered') || $isDelivery) {
                $driverOrder->update(['is_completed' => true]);
                if ($paymentMethod == PaymentMethod::CASH->value) {
                    $driverOrder->update(['cash_collect' => true]);
                    $totalCashCollected = $driverOrder->driver->total_cash_collected + ($order->payable_amount - $order->paid_amount);
                    $driverOrder->driver->update([
                        'total_cash_collected' => $totalCashCollected,
                    ]);
                }
                $setting = PreOrderSetting::first();
                $commission = 0;
                if ($setting?->commission_type != 'fixed') {
                    $commission = $order->total_amount * $setting?->commission / 100;
                } else {
                    $commission = $setting?->commission ?? 0;
                }
                $order->update([
                    'delivery_date' => now(),
                    'delivered_at' => now(),
                    'payment_status' => PrePaymentStatus::PAID->value,
                    'admin_commission' => $commission,
                ]);
                $wallet = $order->shop->user->wallet;
                if (!$wallet) {
                    $wallet =  WalletRepository::storeByRequest($order->shop->user);
                }
                WalletRepository::updateByRequest($wallet, $order->total_amount, 'credit');
                TransactionRepository::storeByRequest($wallet, $commission, 'debit', true, true, 'admin commission added', 'pre-order commission added in admin wallet');
                $driverWallet = DriverRepository::getWallet($driverOrder->driver);
                $deliveryCharge = $order->delivery_charge ?? 0;
                WalletRepository::updateByRequest($driverWallet, $deliveryCharge, 'credit');
                self::decreaseStockOnDelivery($order);
            }
            $user = $order->customer?->user;
            $message = "Hello {$user?->name}. Your order status is {$orderStatus}. OrderID: {$order->order_code}";
            $title = 'PreOrder Status Update';
            $devices = $user?->devices;
            if (count($devices) > 0) {
                $deviceKeys = $devices->pluck('key')->toArray();
                try {
                    NotificationServices::sendNotification($message, $deviceKeys, $title);
                } catch (\Throwable $th) {
                }
            }
            NotificationRepository::storeByRequest((object) [
                'title' => $title,
                'content' => $message,
                'user_id' => $user?->id,
                'url' => $order->id,
                'type' => 'preOrder',
                'icon' => null,
                'is_read' => false,
            ]);
        });
    }

    public static function allowedNextStatuses(PreOrderStatus $currentStatus): array
    {
        return match ($currentStatus) {
            PreOrderStatus::REQUESTED => [
                PreOrderStatus::ACCEPTED,
                PreOrderStatus::IN_SHIPPING,
                PreOrderStatus::DELIVERED,
                PreOrderStatus::CANCELLED,
            ],
            PreOrderStatus::ACCEPTED => [
                PreOrderStatus::IN_SHIPPING,
                PreOrderStatus::DELIVERED,
                PreOrderStatus::CANCELLED,
            ],
            PreOrderStatus::PREPAYMENT_REQUESTED => [
                PreOrderStatus::PREPAYMENT_CONFIRMED,
                PreOrderStatus::IN_SHIPPING,
                PreOrderStatus::DELIVERED,
                PreOrderStatus::CANCELLED,
            ],
            PreOrderStatus::PREPAYMENT_CONFIRMED => [
                PreOrderStatus::IN_SHIPPING,
                PreOrderStatus::DELIVERED,
                PreOrderStatus::CANCELLED,
            ],
            PreOrderStatus::IN_SHIPPING => [
                PreOrderStatus::DELIVERED,
            ],
            PreOrderStatus::DELIVERED => [
                PreOrderStatus::REFUNDED,
            ],
            PreOrderStatus::REFUNDED, PreOrderStatus::CANCELLED => [],
            default => [],
        };
    }
    public static function updateWalletAndTransaction(PreOrder $preOrder)
    {

        $setting = PreOrderSetting::first();
        $commission = 0;

        if ($setting?->commission_type != 'fixed') {
            $commission = $preOrder->total_amount * $setting?->shop_commission / 100;
        } else {
            $commission = $setting?->shop_commission ?? 0;
        }

        $preOrder->update([
            'delivery_date' => now(),
            'delivered_at' => now(),
            'admin_commission' => $commission,
        ]);

        $wallet = $preOrder->shop->user->wallet;
        if (!$wallet) {
            $wallet = WalletRepository::storeByRequest($preOrder->shop->user);
        }

        WalletRepository::updateByRequest($wallet, $preOrder->total_amount, 'credit');

        TransactionRepository::storeByRequest($wallet, $commission, 'debit', true, true, 'admin commission', 'order');
    }

    /**
     * Reduce the pre-ordered product's stock when the order is delivered.
     * Stock is never allowed to go below zero.
     */
    public static function decreaseStockOnDelivery(PreOrder $preOrder): void
    {
        $item = $preOrder->preOrderItem;
        $product = $item?->product;
        if (! $product) {
            return;
        }
        $newQuantity = max(0, (int) $product->quantity - (int) $item->quantity);
        $product->update(['quantity' => $newQuantity]);
    }

    /**
     * Return the pre-ordered product's stock when a delivered order is refunded.
     */
    public static function restoreStockOnRefund(PreOrder $preOrder): void
    {
        $item = $preOrder->preOrderItem;
        $product = $item?->product;
        if (! $product) {
            return;
        }
        $product->increment('quantity', (int) $item->quantity);
    }

    /**
     * Process a customer refund for a pre-order.
     *
     * Two entry points share this:
     *  - admin approves & pays a post-delivery refund request (order is Delivered) →
     *    the shop was already credited on delivery, so reverse the shop wallet and
     *    credit the recorded admin commission back, then mark the order Refunded.
     *  - admin cancels a pre-order before delivery while money was already paid →
     *    the shop was never credited, so only record the refund; order_status stays
     *    whatever the caller set (Cancelled).
     *
     * @param  bool  $markRefundedStatus  set order_status to Refunded (post-delivery flow)
     */
    public static function processRefund(PreOrder $preOrder, bool $markRefundedStatus = true): void
    {
        DB::transaction(function () use ($preOrder, $markRefundedStatus) {
            // The shop wallet is credited only on delivery (delivered_at is stamped
            // there). Reverse it only when that credit actually happened — this is
            // independent of the order_status the caller may have already changed.
            $wasCredited = ! is_null($preOrder->delivered_at);
            $refundAmount = $preOrder->paidTotal();

            if ($wasCredited && $refundAmount > 0) {
                $wallet = $preOrder->shop?->user?->wallet;
                if ($wallet) {
                    WalletRepository::updateByRequest($wallet, $preOrder->total_amount, 'debit');
                    $commission = $preOrder->admin_commission ?? 0;
                    if ($commission > 0) {
                        TransactionRepository::storeByRequest($wallet, $commission, 'credit', true, true, 'pre-order refund', 'admin commission reversed on pre-order refund');
                    }
                }
            }

            // A delivered order took stock out; refunding it puts that stock back.
            // Cancel-before-delivery (markRefundedStatus=false) never reduced stock.
            if ($markRefundedStatus) {
                self::restoreStockOnRefund($preOrder);
            }

            $update = [
                'refund_status' => RefundStatus::REFUNDED->value,
                'refund_amount' => $refundAmount,
                'refunded_at' => now(),
            ];
            if ($markRefundedStatus) {
                $update['order_status'] = PreOrderStatus::REFUNDED->value;
            }
            $preOrder->update($update);

            $user = $preOrder->customer?->user;
            $message = "Hello {$user?->name}. A refund of {$preOrder->currency_symbol}{$refundAmount} has been processed for your pre-order. OrderID: {$preOrder->order_code}";
            $title = 'PreOrder Refund Processed';
            $devices = $user?->devices;
            if ($devices && count($devices) > 0) {
                $deviceKeys = $devices->pluck('key')->toArray();
                try {
                    NotificationServices::sendNotification($message, $deviceKeys, $title);
                } catch (\Throwable $th) {
                }
            }
            NotificationRepository::storeByRequest((object) [
                'title' => $title,
                'content' => $message,
                'user_id' => $user?->id,
                'url' => $preOrder->id,
                'type' => 'preOrder',
                'icon' => null,
                'is_read' => false,
            ]);
        });
    }
}
