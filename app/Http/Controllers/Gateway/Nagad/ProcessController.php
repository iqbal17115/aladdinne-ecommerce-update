<?php

namespace App\Http\Controllers\Gateway\Nagad;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Support\Str;

class ProcessController extends Controller
{
    /**
     * Process to Nagad
     *
     * Follows the Nagad Payment Gateway (PGW) checkout flow:
     * initialize -> complete -> redirect the customer to Nagad's callBackUrl.
     *
     * @return string
     */
    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        $config = json_decode($paymentGateway->config);

        if (empty($config->merchant_id) || empty($config->public_key) || empty($config->private_key)) {
            return json_encode(['error' => 'Nagad gateway is not configured correctly']);
        }

        $baseUrl = self::baseUrl($paymentGateway);
        $merchantId = trim($config->merchant_id);
        // Nagad order id must be unique per attempt.
        $orderId = 'ord'.str_pad($payment->id, 6, '0', STR_PAD_LEFT).substr((string) $payment->id, -2).date('His');
        $dateTime = date('YmdHis');

        // Step 1 — initialize the checkout.
        $sensitive = [
            'merchantId' => $merchantId,
            'datetime' => $dateTime,
            'orderId' => $orderId,
            'challenge' => Str::random(40),
        ];

        $initPayload = [
            'accountNumber' => $config->merchant_number ?? '',
            'dateTime' => $dateTime,
            'sensitiveData' => self::encrypt(json_encode($sensitive), $config->public_key),
            'signature' => self::sign(json_encode($sensitive), $config->private_key),
        ];

        $initUrl = $baseUrl."/api/dfs/check-out/initialize/{$merchantId}/{$orderId}";
        $initResponse = self::post($initUrl, $initPayload, self::headers());

        if (! $initResponse || empty($initResponse->sensitiveData) || empty($initResponse->signature)) {
            return json_encode(['error' => $initResponse->message ?? $initResponse->reason ?? 'Nagad initialization failed']);
        }

        $decoded = json_decode(self::decrypt($initResponse->sensitiveData, $config->private_key));

        if (! $decoded || empty($decoded->paymentReferenceId) || empty($decoded->challenge)) {
            return json_encode(['error' => 'Nagad initialization response invalid']);
        }

        $paymentReferenceId = $decoded->paymentReferenceId;

        // Persist the reference so the callback can verify against it.
        $payment->update(['payment_token' => $paymentReferenceId]);

        // Step 2 — complete/confirm the order.
        $callbackUrl = route('nagad.payment.callback', ['payment' => $payment->id]);

        $orderSensitive = [
            'merchantId' => $merchantId,
            'orderId' => $orderId,
            'currencyCode' => '050', // BDT
            'amount' => (string) round($payment->amount, 2),
            'challenge' => $decoded->challenge,
        ];

        $completePayload = [
            'sensitiveData' => self::encrypt(json_encode($orderSensitive), $config->public_key),
            'signature' => self::sign(json_encode($orderSensitive), $config->private_key),
            'merchantCallbackURL' => $callbackUrl,
        ];

        $completeUrl = $baseUrl."/api/dfs/check-out/complete/{$paymentReferenceId}";
        $completeResponse = self::post($completeUrl, $completePayload, self::headers());

        if ($completeResponse && isset($completeResponse->status) && $completeResponse->status === 'Success' && ! empty($completeResponse->callBackUrl)) {
            return $completeResponse->callBackUrl;
        }

        return json_encode(['error' => $completeResponse->message ?? 'Nagad payment could not be started']);
    }

    /**
     * Resolve the sandbox/live base url.
     */
    public static function baseUrl(PaymentGateway $paymentGateway): string
    {
        $config = json_decode($paymentGateway->config);

        if (! empty($config->base_url)) {
            return rtrim($config->base_url, '/');
        }

        return $paymentGateway->mode === 'live'
            ? 'https://api.mynagad.com'
            : 'https://api.mynagad.com/remote-payment-gateway-1.0/api/dfs';
    }

    /**
     * Nagad required request headers.
     */
    public static function headers(): array
    {
        return [
            'Content-Type: application/json',
            'X-KM-Api-Version: v-0.2.0',
            'X-KM-IP-V4: '.(request()->ip() ?? '127.0.0.1'),
            'X-KM-Client-Type: PC_WEB',
        ];
    }

    /**
     * RSA encrypt with Nagad public key (base64).
     */
    public static function encrypt(string $data, string $publicKey): string
    {
        $pem = self::formatKey($publicKey, 'PUBLIC');
        openssl_public_encrypt($data, $encrypted, $pem, OPENSSL_PKCS1_PADDING);

        return base64_encode($encrypted);
    }

    /**
     * RSA decrypt with the merchant private key.
     */
    public static function decrypt(string $data, string $privateKey): string
    {
        $pem = self::formatKey($privateKey, 'PRIVATE');
        openssl_private_decrypt(base64_decode($data), $decrypted, $pem, OPENSSL_PKCS1_PADDING);

        return (string) $decrypted;
    }

    /**
     * SHA256 signature with the merchant private key (base64).
     */
    public static function sign(string $data, string $privateKey): string
    {
        $pem = self::formatKey($privateKey, 'PRIVATE');
        openssl_sign($data, $signature, $pem, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    /**
     * Wrap a raw base64 key body in PEM headers if not already present.
     */
    private static function formatKey(string $key, string $type): string
    {
        $key = trim($key);

        if (Str::contains($key, 'BEGIN')) {
            return $key;
        }

        $header = $type === 'PUBLIC' ? 'PUBLIC KEY' : 'PRIVATE KEY';
        // Normalise: strip any whitespace/newlines the admin may have pasted, then re-wrap.
        $body = preg_replace('/\s+/', '', $key);

        return "-----BEGIN {$header}-----\n".chunk_split($body, 64, "\n")."-----END {$header}-----";
    }

    /**
     * POST JSON to Nagad and return the decoded response.
     */
    public static function post(string $url, array $data, array $header)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result);
    }

    /**
     * GET JSON from Nagad and return the decoded response.
     */
    public static function get(string $url, array $header)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result);
    }
}
