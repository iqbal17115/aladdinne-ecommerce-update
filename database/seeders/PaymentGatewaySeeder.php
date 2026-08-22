<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $paymentMethods = [
            [
                'title' => 'Stripe',
                'name' => 'stripe',
                'config' => json_encode([
                    'secret_key' => '',
                    'published_key' => '',
                ]),
                'mode' => 'test',
                'alias' => 'Stripe',
                'is_active' => true,
            ],
            [
                'title' => 'PayPal',
                'name' => 'paypal',
                'config' => json_encode([
                    'client_id' => '',
                    'client_secret' => '',
                ]),
                'mode' => 'test',
                'alias' => 'PayPal',
                'is_active' => true,
            ],
            [
                'title' => 'Razorpay',
                'name' => 'razorpay',
                'config' => json_encode([
                    'key' => '',
                    'secret' => '',
                ]),
                'mode' => 'test',
                'alias' => 'Razorpay',
                'is_active' => true,
            ],
            [
                'title' => 'Paystack',
                'name' => 'paystack',
                'config' => json_encode([
                    'public_key' => '',
                    'secret_key' => '',
                    'machant_email' => '',
                ]),
                'mode' => 'test',
                'alias' => 'PayStack',
                'is_active' => true,
            ],
            [
                'title' => 'aamarPay',
                'name' => 'aamarpay',
                'config' => json_encode([
                    'store_id' => 'aamarpaytest',
                    'signature_key' => '',
                ]),
                'mode' => 'test',
                'alias' => 'AamarPay',
                'is_active' => true,
            ],
            [
                'title' => 'BKash',
                'name' => 'bKash',
                'config' => json_encode([
                    'username' => '',
                    'password' => '',
                    'app_key' => '',
                    'app_secret_key' => '',
                ]),
                'mode' => 'test',
                'alias' => 'Bkash',
                'is_active' => true,
            ],
            [
                'title' => 'PayTabs',
                'name' => 'paytabs',
                'config' => json_encode([
                    'base_url' => 'https://secure-global.paytabs.com',
                    'profile_id' => '142160',
                    'server_key' => '',
                    'currency' => 'USD',
                ]),
                'mode' => 'test',
                'alias' => 'PayTabs',
                'is_active' => true,
            ],
            [
                'title' => 'QiCard',
                'name' => 'qicard',
                'config' => json_encode([
                    'terminalId' => '237984',
                    'username' => 'paymentgatewaytest',
                    'password' => '',
                    'currency' => 'IQD',
                ]),
                'mode' => 'test',
                'alias' => 'QiCard',
                'is_active' => true,
            ],
            [
                'title' => 'PayU',
                'name' => 'payu',
                'config' => json_encode([
                    'merchant_key' => '',
                    'merchant_salt' => '',
                    'base_url' => 'https://secure.payu.in/_payment',
                ]),
                'mode' => 'test',
                'alias' => 'PayU',
                'is_active' => true,
            ],
            [
                'title' => 'CashFree',
                'name' => 'cashfree',
                'config' => json_encode([
                    'app_id' => '',
                    'secret_key' => '',
                    'base_url' => 'https://api.cashfree.com/pg/orders',
                ]),
                'mode' => 'test',
                'alias' => 'CashFree',
                'is_active' => true,
            ],
            [
                'title' => 'JazzCash',
                'name' => 'jazzcash',
                'config' => json_encode([
                    'merchant_id' => 'MC651136',
                    'password' => '',
                    'integrity_salt' => '',
                    'base_url' => 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform',
                    'note' => 'You have to setup this return URL in your JazzCash merchant account dashboard: '.route('payment.success.post'),
                ]),
                'mode' => 'test',
                'alias' => 'JazzCash',
                'is_active' => true,
            ],
            [
                'title' => 'Nagad',
                'name' => 'nagad',
                'config' => json_encode([
                    'merchant_id' => '',
                    'merchant_number' => '',
                    'public_key' => '',
                    'private_key' => '',
                    'note' => 'Provide the Nagad merchant id, merchant number, Nagad public key and your merchant private key (PEM body only). Callback URL is handled automatically.',
                ]),
                'mode' => 'test',
                'alias' => 'Nagad',
                'is_active' => true,
            ],
            [
                'title' => 'Rocket',
                'name' => 'rocket',
                'config' => json_encode([
                    'merchant_id' => '',
                    'api_key' => '',
                    'base_url' => 'https://sandbox.rocket.com.bd/checkout',
                    'note' => 'Rocket (DBBL) is served through a hosted checkout. Set the base_url, merchant id and api key provided by your Rocket/DBBL merchant account. Return URL to configure on their dashboard: '.route('payment.success.post'),
                ]),
                'mode' => 'test',
                'alias' => 'Rocket',
                'is_active' => true,
            ],
            [
                'title' => 'SSLCommerz',
                'name' => 'sslcommerz',
                'config' => json_encode([
                    'store_id' => 'testbox',
                    'store_passwd' => '',
                    'currency' => 'BDT',
                    'note' => 'Default values are the SSLCommerz sandbox test store. Replace store_id and store_passwd with your live merchant credentials before going live.',
                ]),
                'mode' => 'test',
                'alias' => 'Sslcommerz',
                'is_active' => true,
            ],
        ];
        foreach ($paymentMethods as $method) {
            $exists = PaymentGateway::where('name', $method['name'])->first();
            if(!$exists) {
                PaymentGateway::create($method);
            }
        }
    }
}
