<?php

namespace App\Http\Controllers\Gateway\Sslcommerz;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    /**
     * SSLCommerz redirects here after a payment attempt succeeds.
     * The amount/transaction are re-validated server-side before marking paid.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Payment $payment, Request $request)
    {
        if ($payment->is_paid) {
            return to_route('order.payment.success', $payment);
        }

        $valId = $request->input('val_id');
        $status = $request->input('status');

        if ($status !== 'VALID' && $status !== 'VALIDATED') {
            return to_route('order.payment.cancel', ['payment' => $payment, 'error' => 'SSLCommerz payment was not successful']);
        }

        $paymentGateway = PaymentGateway::where('name', 'sslcommerz')->first();

        if (! $paymentGateway || empty($valId)) {
            return to_route('order.payment.cancel', ['payment' => $payment, 'error' => 'SSLCommerz validation could not be performed']);
        }

        $config = json_decode($paymentGateway->config);

        $query = http_build_query([
            'val_id' => $valId,
            'store_id' => trim($config->store_id),
            'store_passwd' => trim($config->store_passwd),
            'format' => 'json',
        ]);

        $response = ProcessController::post(ProcessController::validationUrl($paymentGateway).'?'.$query, []);

        $validStatus = $response->status ?? null;
        $validAmount = isset($response->amount) ? round((float) $response->amount, 2) : null;
        $tranMatches = ($response->tran_id ?? null) === $payment->payment_token;

        if (($validStatus === 'VALID' || $validStatus === 'VALIDATED')
            && $tranMatches
            && $validAmount !== null
            && $validAmount >= round($payment->amount, 2)) {
            return to_route('payment.success', ['payment' => $payment->id]);
        }

        return to_route('order.payment.cancel', ['payment' => $payment, 'error' => 'SSLCommerz payment validation failed']);
    }

    /**
     * SSLCommerz redirects here when the payment fails.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fail(Payment $payment)
    {
        return to_route('order.payment.cancel', ['payment' => $payment, 'error' => 'SSLCommerz payment failed']);
    }

    /**
     * SSLCommerz redirects here when the customer cancels.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Payment $payment)
    {
        return to_route('order.payment.cancel', ['payment' => $payment, 'error' => 'SSLCommerz payment cancelled']);
    }

    /**
     * Server-to-server IPN notification.
     *
     * @return \Illuminate\Http\Response
     */
    public function ipn(Payment $payment, Request $request)
    {
        $status = $request->input('status');

        if (($status === 'VALID' || $status === 'VALIDATED') && ! $payment->is_paid) {
            $paymentGateway = PaymentGateway::where('name', 'sslcommerz')->first();

            if ($paymentGateway && $request->filled('val_id')) {
                $config = json_decode($paymentGateway->config);
                $query = http_build_query([
                    'val_id' => $request->input('val_id'),
                    'store_id' => trim($config->store_id),
                    'store_passwd' => trim($config->store_passwd),
                    'format' => 'json',
                ]);

                $response = ProcessController::post(ProcessController::validationUrl($paymentGateway).'?'.$query, []);
                $validStatus = $response->status ?? null;

                if (($validStatus === 'VALID' || $validStatus === 'VALIDATED')
                    && ($response->tran_id ?? null) === $payment->payment_token) {
                    $payment->orders()->update(['payment_status' => \App\Enums\PaymentStatus::PAID->value]);
                    $payment->update(['is_paid' => true]);
                }
            }
        }

        return response('IPN received', 200);
    }
}
