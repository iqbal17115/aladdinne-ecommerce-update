<?php

namespace Modules\PreOrder\App\Enums;

enum PrePaymentStatus: string
{
    case PENDING = 'Pending';
    case PREPAID = 'Prepaid';
    case PAID = 'Paid';
}
