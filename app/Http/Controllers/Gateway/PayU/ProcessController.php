<?php

namespace App\Http\Controllers\Gateway\PayU;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProcessController extends Controller
{
    public function index(Request $request, $id)
    {
        $id = decrypt($id);
        $payment = Payment::findOrFail($id);

        $paymentGateway = PaymentGateway::where('name', 'PayU')->first();
        $config = json_decode($paymentGateway->config);

        $paymentToken = Str::uuid()->toString();
        $url =  $config->base_url;

        $payment->update(['payment_token' => $paymentToken]);

        $successUrl = route('payment.success', $payment->id);
        $cancelUrl = route('payment.cancel', $payment->id);

        $key = $config->merchant_key;
        $salt = $config->merchant_salt;

        $productinfo = 'Product_info'.$id;
        $firstname = 'John Doe';
        $email = 'admin@example.com';


        $hashString = $key . '|' . $paymentToken . '|' . $payment->amount . '|' . $productinfo . '|' .
                  $firstname . '|' . $email . '|||||||||||' . $salt;

        $hash = strtolower(hash('sha512', $hashString));

        // Prepare form data
        $data = [
            'key'         => $key,
            'txnid'       => $paymentToken,
            'amount'      => $payment->amount,
            'productinfo' => $productinfo,
            'firstname'   => $firstname,
            'email'       => $email,
            'phone'       => '9999999999',
            'furl'        => $cancelUrl,
            'surl'        => $successUrl,
            'hash'        => $hash,
            'service_provider' => 'payu_paisa',
        ];
        // Render a Blade view with an auto-submitting form
        return view('payment.payu', compact('data', 'url'));
    }

    /**
     * Process to stripe
     *
     * @return string
     */
    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        return route('pay-via.payu', encrypt($payment->id));
    }
}
