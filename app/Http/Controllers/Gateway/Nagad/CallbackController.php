<?php

namespace App\Http\Controllers\Gateway\Nagad;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    /**
     * Handle the redirect back from Nagad and verify the payment.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(Payment $payment, Request $request)
    {
        $status = $request->query('status');
        $paymentRefId = $request->query('payment_ref_id');

        if ($status !== 'Success' || empty($paymentRefId)) {
            return to_route('order.payment.cancel', ['payment' => $payment, 'error' => 'Nagad payment was not completed']);
        }

        $paymentGateway = PaymentGateway::where('name', 'nagad')->first();

        if (! $paymentGateway) {
            return to_route('order.payment.cancel', ['payment' => $payment, 'error' => 'Nagad gateway not found']);
        }

        $baseUrl = ProcessController::baseUrl($paymentGateway);
        $verifyUrl = $baseUrl."/api/dfs/verify/payment/{$paymentRefId}";

        $response = ProcessController::get($verifyUrl, ProcessController::headers());

        if ($response && isset($response->status) && $response->status === 'Success') {
            return to_route('payment.success', ['payment' => $payment->id]);
        }

        return to_route('order.payment.cancel', ['payment' => $payment, 'error' => $response->message ?? 'Nagad payment verification failed']);
    }
}
