<?php

namespace Modules\PreOrder\App\Enums;

enum RefundStatus: string
{
    case REQUESTED = 'Requested';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case REFUNDED = 'Refunded';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => 'Refund Requested',
            self::APPROVED => 'Refund Approved',
            self::REJECTED => 'Refund Rejected',
            self::REFUNDED => 'Refunded',
        };
    }
}
