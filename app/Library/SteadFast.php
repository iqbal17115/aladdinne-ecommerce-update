<?php

namespace App\Library;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteadFast
{
    private $api_key;
    private $secret_key;
    private $base_url;

    public function __construct()
    {
        $this->api_key = config('steadfast.api_key');
        $this->secret_key = config('steadfast.secret_key');
        $this->base_url = config('steadfast.base_url', 'https://portal.packzy.com/api/v1');
    }

    /**
     * Get request headers
     */
    private function getHeaders()
    {
        return [
            'Api-Key' => $this->api_key,
            'Secret-Key' => $this->secret_key,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Create a single order
     */
    public function createOrder(array $orderData)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($this->base_url.'/create_order', $orderData);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('SteadFast API Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'API request failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Create bulk orders (max 500 items)
     */
    public function bulkCreate($orders)
    {
        try {
            if (count($orders) > config('steadfast.max_bulk_orders', 500)) {
                return [
                    'success' => false,
                    'message' => 'Maximum 500 items are allowed for bulk order',
                ];
            }

            $response = Http::withHeaders($this->getHeaders())
                ->post($this->base_url.'/create_order/bulk-order', [
                    'data' => json_encode($orders),
                ]);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('SteadFast Bulk API Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Bulk API request failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check delivery status by consignment ID
     */
    public function getStatusByConsignmentId($consignmentId)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->base_url.'/status_by_cid/'.$consignmentId);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('SteadFast Status API Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Status API request failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check delivery status by invoice ID
     */
    public function getStatusByInvoice($invoice)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->base_url.'/status_by_invoice/'.$invoice);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('SteadFast Status API Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Status API request failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check delivery status by tracking code
     */
    public function getStatusByTrackingCode($trackingCode)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->base_url.'/status_by_trackingcode/'.$trackingCode);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('SteadFast Status API Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Status API request failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get current balance
     */
    public function getBalance()
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->base_url.'/get_balance');

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('SteadFast Balance API Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Balance API request failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Handle API response
     */
    private function handleResponse($response)
    {
        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
                'status_code' => $response->status(),
            ];
        }

        return [
            'success' => false,
            'message' => 'API request failed',
            'status_code' => $response->status(),
            'error' => $response->body(),
        ];
    }

    /**
     * Validate order data
     */
    public function validateOrderData($orderData)
    {
        $required = ['invoice', 'recipient_name', 'recipient_phone', 'recipient_address', 'cod_amount'];
        $missing = [];

        foreach ($required as $field) {
            if (! isset($orderData[$field]) || empty($orderData[$field])) {
                $missing[] = $field;
            }
        }

        if (! empty($missing)) {
            return [
                'valid' => false,
                'message' => 'Missing required fields: '.implode(', ', $missing),
            ];
        }

        if (! preg_match('/^[0-9]{11}$/', $orderData['recipient_phone'])) {
            return [
                'valid' => false,
                'message' => 'Phone number must be 11 digits',
            ];
        }

        if ($orderData['cod_amount'] < 0) {
            return [
                'valid' => false,
                'message' => 'COD amount cannot be less than 0',
            ];
        }

        return ['valid' => true];
    }

    /**
     * Get delivery status descriptions
     */
    public function getDeliveryStatusDescriptions()
    {
        return [
            'pending' => 'Consignment is not delivered or cancelled yet.',
            'delivered_approval_pending' => 'Consignment is delivered but waiting for admin approval.',
            'partial_delivered_approval_pending' => 'Consignment is delivered partially and waiting for admin approval.',
            'cancelled_approval_pending' => 'Consignment is cancelled and waiting for admin approval.',
            'unknown_approval_pending' => 'Unknown Pending status. Need contact with the support team.',
            'delivered' => 'Consignment is delivered and balance added.',
            'partial_delivered' => 'Consignment is partially delivered and balance added.',
            'cancelled' => 'Consignment is cancelled and balance updated.',
            'hold' => 'Consignment is held.',
            'in_review' => 'Order is placed and waiting to be reviewed.',
            'unknown' => 'Unknown status. Need contact with the support team.',
        ];
    }
}
