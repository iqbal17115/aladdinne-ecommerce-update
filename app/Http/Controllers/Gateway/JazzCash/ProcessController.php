<?php

namespace App\Http\Controllers\Gateway\JazzCash;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;

class ProcessController extends Controller
{
    public function index($id)
    {
        $id = decrypt($id);
        $payment = Payment::findOrFail($id);

        $paymentGateway = PaymentGateway::where('name', 'JazzCash')->first();
        $config = json_decode($paymentGateway->config);
        $clean = fn($v) => trim(str_replace(["\n", "\r", "\t"], '', (string)$v));

        $url       = $clean($config->base_url);
        $merchantId    = $clean($config->merchant_id);
        $password      = $clean($config->password);
        $integritySalt = $clean($config->integrity_salt);

        $txnDateTime = date('YmdHis');
        $txnRefNo    = "T" . $txnDateTime;
        $txnExpiry   = date('YmdHis', strtotime('+1 Day'));
        $successUrl = route('payment.success.post') . '?payment=' . $payment->id;
        $orderId = mt_rand(1000, 9999);
        $payload = [
            "pp_Version" => "1.1",
            "pp_TxnType" => "MWALLET",
            "pp_Language" => "EN",
            "pp_MerchantID" => $merchantId,
            "pp_Password" => $password,
            "pp_TxnRefNo" => $txnRefNo,
            "pp_Amount" => (int) round($payment->amount * 100),
            "pp_TxnCurrency" => "PKR",
            "pp_TxnDateTime" => $txnDateTime,
            "pp_BillReference" => "billRef",
            "pp_Description" => "Payment Order " . $payment->id,
            "pp_TxnExpiryDateTime" => $txnExpiry,
            "pp_ReturnURL" => $successUrl,
        ];
        ksort($payload);

        $hashString = $integritySalt;

        foreach ($payload as $value) {
            $hashString .= '&' . $value;
        }

        $secureHash = strtoupper(hash_hmac('sha256', $hashString, $integritySalt));
        $payload['pp_SecureHash'] = $secureHash;

        return view('payment.jazzcash', compact('payload', 'url'));
    }

    /**
     * Process to Paystack
     *
     * @return string
     */

    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        return route('pay-via.jazzcash', encrypt($payment->id));

    }
}
