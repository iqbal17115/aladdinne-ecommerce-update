<?php

namespace App\Http\Controllers\Gateway\CashFree;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessController extends Controller
{
    /**
     * Process to Paystack
     *
     * @return string
     */

    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        $config = json_decode($paymentGateway->config);

        $orderId = "order_" . $payment->id . Str::uuid();
        $amount = round($payment->amount, 2);
        // $successUrl = 'https://razinsoft.com';
        $successUrl = route('payment.success', $payment->id);
        $cancelUrl = route('payment.cancel', $payment->id);
        $appId = trim(str_replace(["\r", "\n"], '', $config->app_id));
        $secretKey = trim(str_replace(["\r", "\n"], '', $config->secret_key));
        $payload = [
            "order_id"         => $orderId,
            "order_amount"     => $amount,
            "order_currency"   => "INR",
            "customer_details" => [
                "customer_id"    => Str::random(8),
                "customer_name"  => $info['name']  ?? "Guest",
                "customer_email" => $info['email'] ?? "guest@mail.com",
                "customer_phone" => $info['phone'] ?? "0000000000",
            ],
            "order_meta" => [
                "return_url" => $successUrl
            ]
        ];

        $orderResponse = Http::withHeaders([
            'x-client-id'     => $appId,
            'x-client-secret' => $secretKey,
            'x-api-version'   => '2025-01-01',
            'Content-Type'    => 'application/json',
        ])->post($config->base_url, $payload);

        if (!$orderResponse->ok()) {
            // Log::error('Cashfree order creation failed', $orderResponse->json());
            return $cancelUrl;
        }

        // Extract order details
        $orderData = $orderResponse->json();

        $sessionResponse = Http::withHeaders([
            'x-client-id'     => $appId,
            'x-client-secret' => $secretKey,
            'x-api-version'   => '2025-01-01',
            'Content-Type'    => 'application/json',
        ])->post("https://api.cashfree.com/pg/orders/sessions", [
            "payment_session_id" => $orderData['payment_session_id'],
            "payment_method"     => [
                "upi" => [
                    "channel" => "link"
                ]
            ]
        ]);
        // Log::error('Cashfree order creation failed', $sessionResponse->json());
        if ($sessionResponse->ok()) {
            $sessionData = $sessionResponse->json();
            return $sessionData['data']['url'];
        }

        return $cancelUrl;
    }
}
