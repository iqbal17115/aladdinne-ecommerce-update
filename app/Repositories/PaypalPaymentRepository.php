<?php

namespace App\Repositories;

use App\Models\PaypalPayment;

class PaypalPaymentRepository extends Repository
{
    public static function model()
    {
        return PaypalPayment::class;
    }
}
