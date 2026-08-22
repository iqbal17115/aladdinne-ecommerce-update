<?php

namespace App\Services;

use App\Library\SteadFast;
use App\Models\Address;
use App\Models\CourierTracking;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\PreOrder\App\Models\PreOrder;

class SteadFastService
{
    protected $steadfast;

    public function __construct()
    {
        $this->steadfast = new SteadFast;
    }

    /**
     * Create courier order from ecommerce order
     */
    public function createCourierOrder(Order $order)
    {
        try {
            DB::beginTransaction();

            $orderData = $this->prepareOrderData($order);

            $validation = $this->steadfast->validateOrderData($orderData);
            if (! $validation['valid']) {
                DB::rollback();

                return [
                    'success' => false,
                    'message' => $validation['message'],
                ];
            }

            $response = $this->steadfast->createOrder($orderData);

            if ($response['success']) {
                $consignmentData = $response['data']['consignment'];

                $this->saveTrackingInfo($order, $consignmentData);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Order sent to courier successfully',
                    'tracking_code' => $consignmentData['tracking_code'],
                    'consignment_id' => $consignmentData['consignment_id'],
                ];
            }

            DB::rollback();

            return $response;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('SteadFast Service Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to create courier order: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get order status by consignment id
     */
    public function getOrder($consignment_id)
    {
        try {
            return $this->steadfast->getStatusByConsignmentId($consignment_id);
        } catch (\Exception $e) {
            Log::error('SteadFast Status: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to update order status: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Update order status from courier
     */
    public function updateOrderStatus($orderId, $method = 'invoice')
    {
        try {
            $order = Order::find($orderId);
            if (! $order) {
                return ['success' => false, 'message' => 'Order not found'];
            }

            $response = null;
            switch ($method) {
                case 'invoice':
                    $response = $this->steadfast->getStatusByInvoice($order->order_code);
                    break;
                case 'consignment':
                    $tracking = CourierTracking::where('order_id', $orderId)->first();
                    if ($tracking) {
                        $response = $this->steadfast->getStatusByConsignmentId($tracking->consignment_id);
                    }
                    break;
            }

            if ($response && $response['success']) {
                $deliveryStatus = $response['data']['delivery_status'];

                $this->updateOrderByDeliveryStatus($order, $deliveryStatus);

                return [
                    'success' => true,
                    'delivery_status' => $deliveryStatus,
                    'message' => 'Order status updated successfully',
                ];
            }

            return $response ?? ['success' => false, 'message' => 'Invalid method'];
        } catch (\Exception $e) {
            Log::error('SteadFast Status Update Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to update order status: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get current balance
     */
    public function getBalance()
    {
        return $this->steadfast->getBalance();
    }

    /**
     * Create courier order from a pre-order
     */
    public function createCourierOrderForPreOrder(PreOrder $preOrder)
    {
        try {
            DB::beginTransaction();

            $orderData = $this->preparePreOrderData($preOrder);

            $validation = $this->steadfast->validateOrderData($orderData);
            if (! $validation['valid']) {
                DB::rollback();

                return [
                    'success' => false,
                    'message' => $validation['message'],
                ];
            }

            $response = $this->steadfast->createOrder($orderData);

            if ($response['success']) {
                $consignmentData = $response['data']['consignment'];

                $this->saveTrackingInfo($preOrder, $consignmentData, true);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Order sent to courier successfully',
                    'tracking_code' => $consignmentData['tracking_code'],
                    'consignment_id' => $consignmentData['consignment_id'],
                ];
            }

            DB::rollback();

            return $response;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('SteadFast Service Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to create courier order: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Prepare order data for SteadFast API
     */
    private function prepareOrderData(Order $order)
    {
        $address = $order->address;
        $user = $order->customer?->user;

        return [
            'invoice' => $order->order_code,
            'recipient_name' => $address->name ?? trim(($user->name ?? '').' '.($user->last_name ?? '')),
            'recipient_phone' => $address->phone ?? $user->phone,
            'alternative_phone' => $user->phone ?? '',
            'recipient_email' => '',
            'recipient_address' => $this->formatAddress($address),
            'cod_amount' => (int) round($order->payable_amount ?? $order->total_amount),
            'note' => $order->instruction ?? '',
            'item_description' => $this->getItemDescription($order),
            'total_lot' => $order->products->sum(fn ($product) => $product->pivot->quantity),
            'delivery_type' => config('steadfast.default_delivery_type', 0),
        ];
    }

    /**
     * Prepare pre-order data for SteadFast API
     */
    private function preparePreOrderData(PreOrder $preOrder)
    {
        $address = $preOrder->address;
        $user = $preOrder->customer?->user;
        $item = $preOrder->preOrderItem;

        return [
            'invoice' => $preOrder->order_code,
            'recipient_name' => $address->name ?? trim(($user->name ?? '').' '.($user->last_name ?? '')),
            'recipient_phone' => $address->phone ?? $user->phone,
            'alternative_phone' => $user->phone ?? '',
            'recipient_email' => '',
            'recipient_address' => $this->formatAddress($address),
            'cod_amount' => (int) round($preOrder->payable_amount ?? $preOrder->total_amount),
            'note' => $preOrder->customer_note ?? '',
            'item_description' => $item ? substr(($item->product_name ?? '').' (Qty: '.$item->quantity.')', 0, 250) : '',
            'total_lot' => $item->quantity ?? 1,
            'delivery_type' => config('steadfast.default_delivery_type', 0),
        ];
    }

    /**
     * Format complete address
     */
    private function formatAddress(Address $address)
    {
        $area = $address->getArea?->name ?? $address->area ?? '';

        $fullAddress = $address->address_line;
        if ($address->address_line2) {
            $fullAddress .= ', '.$address->address_line2;
        }
        if ($area) {
            $fullAddress .= ', '.$area;
        }
        if ($address->post_code) {
            $fullAddress .= '-'.$address->post_code;
        }

        return substr($fullAddress, 0, 250);
    }

    /**
     * Get item description from order items
     */
    private function getItemDescription(Order $order)
    {
        $items = $order->products->map(function ($item) {
            return ($item->name ?? '').' (Qty: '.$item->pivot->quantity.')';
        })->implode(', ');

        return substr($items, 0, 250);
    }

    /**
     * Save tracking information
     */
    private function saveTrackingInfo($order, $consignmentData, bool $isPreOrder = false)
    {
        CourierTracking::create([
            'order_id' => $isPreOrder ? null : $order->id,
            'pre_order_id' => $isPreOrder ? $order->id : null,
            'consignment_id' => $consignmentData['consignment_id'],
            'tracking_code' => $consignmentData['tracking_code'],
            'courier_name' => 'SteadFast',
            'status' => $consignmentData['status'],
            'delivery_fee' => $consignmentData['delivery_fee'] ?? 0,
        ]);
    }

    /**
     * Update order based on delivery status
     */
    private function updateOrderByDeliveryStatus(Order $order, $deliveryStatus)
    {
        $statusMapping = [
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];

        if (isset($statusMapping[$deliveryStatus])) {
            $order->update(['order_status' => $statusMapping[$deliveryStatus]]);
        }

        CourierTracking::where('order_id', $order->id)
            ->update(['status' => $deliveryStatus]);
    }
}
