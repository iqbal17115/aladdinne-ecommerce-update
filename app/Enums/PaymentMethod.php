<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'Cash Payment';
    case ONLINE = 'Online Payment';
    case STRIPE = 'Stripe';
    case PAYPAL = 'PayPal';
    case RAZORPAY = 'Razorpay';
    case PAYSTACK = 'PayStack';
    case AAMARPAY = 'Amarpay';
    case BKASH = 'Bkash';
    case PAYTABS = 'PayTabs';
    case QICARD = 'QiCard';
    case PAYU = 'PayU';
    case CASHFREE = 'cashFree';
    case JAZZCASH = 'JazzCash';
    case NAGAD = 'Nagad';
    case ROCKET = 'Rocket';
    case SSLCOMMERZ = 'SslCommerz';
}
