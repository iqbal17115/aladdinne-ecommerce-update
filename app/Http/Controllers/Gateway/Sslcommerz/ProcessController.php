<?php

namespace App\Http\Controllers\Gateway\Sslcommerz;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;

class ProcessController extends Controller
{
    /**
     * Process to SSLCommerz.
     *
     * Initiates a session and returns the hosted GatewayPageURL the
     * customer should be redirected to.
     *
     * @return string
     */
    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        $config = json_decode($paymentGateway->config);

        if (empty($config->store_id) || empty($config->store_passwd)) {
            return json_encode(['error' => 'SSLCommerz gateway is not configured correctly']);
        }

        $customer = self::customer($payment);
        $tranId = 'ssl-'.$payment->id.'-'.date('YmdHis');

        // Persist the transaction id so the validation callback can match it.
        $payment->update(['payment_token' => $tranId]);

        $postData = [
            'store_id' => trim($config->store_id),
            'store_passwd' => trim($config->store_passwd),
            'total_amount' => round($payment->amount, 2),
            'currency' => $config->currency ?? 'BDT',
            'tran_id' => $tranId,
            'success_url' => route('sslcommerz.payment.success', ['payment' => $payment->id]),
            'fail_url' => route('sslcommerz.payment.fail', ['payment' => $payment->id]),
            'cancel_url' => route('sslcommerz.payment.cancel', ['payment' => $payment->id]),
            'ipn_url' => route('sslcommerz.payment.ipn', ['payment' => $payment->id]),
            'shipping_method' => 'NO',
            'product_name' => 'Order #'.$payment->id,
            'product_category' => 'General',
            'product_profile' => 'general',
            'cus_name' => $customer['name'],
            'cus_email' => $customer['email'],
            'cus_add1' => $customer['address'],
            'cus_city' => $customer['city'],
            'cus_postcode' => $customer['postcode'],
            'cus_country' => 'Bangladesh',
            'cus_phone' => $customer['phone'],
            'num_of_item' => 1,
        ];

        $response = self::post(self::baseUrl($paymentGateway), $postData);

        if ($response && isset($response->status) && $response->status === 'SUCCESS' && ! empty($response->GatewayPageURL)) {
            return $response->GatewayPageURL;
        }

        $error = $response->failedreason ?? 'SSLCommerz session could not be created';

        return json_encode(['error' => $error]);
    }

    /**
     * Resolve the sandbox/live initiation url.
     */
    public static function baseUrl(PaymentGateway $paymentGateway): string
    {
        return $paymentGateway->mode === 'live'
            ? 'https://securepay.sslcommerz.com/gwprocess/v4/api.php'
            : 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';
    }

    /**
     * Resolve the validation api url.
     */
    public static function validationUrl(PaymentGateway $paymentGateway): string
    {
        return $paymentGateway->mode === 'live'
            ? 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php'
            : 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php';
    }

    /**
     * Best-effort customer details pulled from the first order, with fallbacks.
     */
    private static function customer(Payment $payment): array
    {
        $order = $payment->orders()->with(['address', 'customer.user'])->first();
        $address = $order?->address;
        $user = $order?->customer?->user;

        return [
            'name' => $address->name ?? $user->name ?? 'Customer',
            'email' => $user->email ?? 'customer@example.com',
            'phone' => $address->phone ?? '01700000000',
            'address' => $address->address_line ?? 'N/A',
            'city' => $address->area ?? 'Dhaka',
            'postcode' => $address->post_code ?? '1000',
        ];
    }

    /**
     * POST form-encoded data to SSLCommerz and return the decoded response.
     */
    public static function post(string $url, array $data)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result);
    }
}
