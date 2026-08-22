<?php

namespace App\Http\Controllers\Gateway\Rocket;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;

class ProcessController extends Controller
{
    /**
     * Render the hosted Rocket checkout and auto-submit to the gateway.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index($id)
    {
        $id = decrypt($id);
        $payment = Payment::findOrFail($id);

        $paymentGateway = PaymentGateway::where('name', 'rocket')->first();
        $config = json_decode($paymentGateway->config);

        if (empty($config->base_url) || empty($config->merchant_id) || empty($config->api_key)) {
            return to_route('order.payment.cancel', ['payment' => $payment, 'error' => 'Rocket gateway is not configured correctly']);
        }

        $url = rtrim($config->base_url, '/');
        $orderId = 'ord'.str_pad($payment->id, 6, '0', STR_PAD_LEFT).date('His');
        $amount = round($payment->amount, 2);

        $returnUrl = route('payment.success.post').'?payment='.$payment->id;
        $cancelUrl = route('order.payment.cancel', ['payment' => $payment->id]);

        $payload = [
            'merchant_id' => trim($config->merchant_id),
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => 'BDT',
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            // Signature the merchant validates on their hosted page.
            'signature' => hash_hmac('sha256', $config->merchant_id.$orderId.$amount, $config->api_key),
        ];

        return view('payment.rocket', compact('payload', 'url'));
    }

    /**
     * Entry point used by the payment dispatcher.
     *
     * @return string
     */
    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        return route('pay-via.rocket', encrypt($payment->id));
    }
}
