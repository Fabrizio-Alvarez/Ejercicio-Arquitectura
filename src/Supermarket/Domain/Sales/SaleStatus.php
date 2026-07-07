<?php

declare(strict_types=1);

namespace Supermarket\Domain\Sales;

enum SaleStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
}
