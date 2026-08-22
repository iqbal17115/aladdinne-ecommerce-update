<?php

namespace Modules\PreOrder\App\Enums;

enum PreOrderStatus: string
{
    case REQUESTED = 'Requested';
    case ACCEPTED = 'Accepted';
    case PREPAYMENT_REQUESTED = 'Prepayment Request';
    case PREPAYMENT_CONFIRMED = 'Confirmed Prepayment';
    case IN_SHIPPING = 'In Shipping';
    case PICKUP = 'Pickup';
    case ON_THE_WAY = 'On The Way';
    case DELIVERED = 'Delivered';
    case CANCELLED = 'Cancelled';
    case REFUNDED = 'Refunded';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => 'Requested',
            self::ACCEPTED => 'Accepted Request',
            self::PREPAYMENT_REQUESTED => 'Prepayment Request',
            self::PREPAYMENT_CONFIRMED => 'Confirmed Prepayment',
            self::IN_SHIPPING => 'In Shipping',
            self::PICKUP => 'Pickup',
            self::ON_THE_WAY => 'On The Way',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refund',
        };
    }
}
