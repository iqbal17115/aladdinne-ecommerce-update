<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\GeneraleSetting;
use App\Models\Order;
use App\Models\User;
use App\Models\CourierTracking;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Services\NotificationServices;
use App\Services\SteadFastService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a order list with filter status.
     */
    public function index($status = null)
    {
        $status = $status ? str_replace('_', ' ', $status) : '';

        $generaleSetting = GeneraleSetting::first();
        $shop = null;
        if ($generaleSetting?->shop_type == 'single') {
            $shop = User::role(Roles::ROOT->value)->first()?->shop;
        }

        $orders = OrderRepository::query()
            ->when($shop, function ($query) use ($shop) {
                return $query->where('shop_id', $shop->id);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('order_status', $status);
            })->latest('id')->paginate(20);

        return view('admin.order.index', compact('orders', 'status'));
    }

    /**
     * Display the order details.
     */
    public function show(Order $order)
    {
        $orderStatus = OrderStatus::cases();

        $riders = Driver::whereHas('user', function ($query) {
            return $query->where('is_active', true);
        })->get();

        $courierTracking = CourierTracking::where('order_id', $order->id)->latest('id')->first();

        return view('admin.order.show', compact('order', 'orderStatus', 'riders', 'courierTracking'));
    }

    /**
     * Send order to SteadFast courier.
     */
    public function assignCourier(Order $order)
    {
        if (! config('steadfast.active')) {
            return back()->with('error', __('SteadFast courier is not active.'));
        }

        if (in_array($order->order_status, [OrderStatus::CANCELLED, OrderStatus::DELIVERED])) {
            return back()->with('error', __('Courier cannot be assigned.'));
        }

        if (CourierTracking::where('order_id', $order->id)->exists()) {
            return back()->with('error', __('Order has already been sent to courier.'));
        }

        $response = (new SteadFastService)->createCourierOrder($order);

        if (! $response['success']) {
            return back()->with('error', $response['message']);
        }

        return back()->with('success', __('Order sent to SteadFast courier successfully.'));
    }

    /**
     * Update the order status.
     */
    public function statusChange(Order $order, Request $request)
    {
        $request->validate(['status' => 'required']);

        $order->update(['order_status' => $request->status]);

        $title = 'Order status updated';
        $message = 'Your order status updated to '.$request->status;
        $deviceKeys = $order->customer->user->devices->pluck('key')->toArray();

        if ($request->status == OrderStatus::CANCELLED->value) {
            foreach ($order->products as $product) {

                $qty = $product->pivot->quantity;

                $product->update(['quantity' => $product->quantity + $qty]);

                $flashSale = $product->flashSales?->first();
                $flashSaleProduct = null;

                if ($flashSale) {
                    $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();

                    if ($flashSaleProduct && $product->pivot?->price) {
                        if ($flashSaleProduct->pivot->sale_quantity >= $qty && ($product->pivot?->price == $flashSaleProduct->pivot->price)) {
                            $flashSale->products()->updateExistingPivot($product->id, [
                                'sale_quantity' => $flashSaleProduct->pivot->sale_quantity - $qty,
                            ]);
                        }
                    }
                }
            }

            if (function_exists('module_exists') && module_exists('Purchase')) {
                $order->productStockOuts()->delete();
            }
        }

        try {
            NotificationServices::sendNotification($message, $deviceKeys, $title);
        } catch (\Throwable $th) {
        }

        $notify = (object) [
            'title' => $title,
            'content' => $message,
            'user_id' => $order->customer->user_id,
            'type' => 'order',
        ];

        NotificationRepository::storeByRequest($notify);

        return back()->with('success', __('Order status updated successfully.'));
    }

    /**
     * Update the payment status.
     */
    public function paymentStatusToggle(Order $order)
    {
        if ($order->payment_status->value == PaymentStatus::PAID->value) {
            return back()->with('error', __('When order is paid, payment status cannot be changed.'));
        }
        $order->update(['payment_status' => PaymentStatus::PAID->value]);

        $title = 'Payment status updated';
        $message = __('Your payment status updated to paid. order code: ').$order->prefix.$order->order_code;
        $deviceKeys = $order->customer->user->devices->pluck('key')->toArray();

        try {
            NotificationServices::sendNotification($message, $deviceKeys, $title);
        } catch (\Throwable $th) {
        }

        $notify = (object) [
            'title' => $title,
            'content' => $message,
            'user_id' => $order->customer->user_id,
            'type' => 'order',
        ];

        NotificationRepository::storeByRequest($notify);

        return back()->with('success', __('Payment status updated successfully'));
    }
}
