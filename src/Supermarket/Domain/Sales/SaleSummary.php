<?php

declare(strict_types=1);

namespace Supermarket\Domain\Sales;

use Supermarket\Domain\Shared\Money;

/**
 * One row of a cash close (cierre de caja): a single confirmed sale,
 * with the customer, the amount+currency charged, and the cashier.
 */
final class SaleSummary
{
    public function __construct(
        private readonly string $saleId,
        private readonly string $customerName,
        private readonly Money $amount,
        private readonly string $cashierId,
    ) {}

    public function saleId(): string
    {
        return $this->saleId;
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function cashierId(): string
    {
        return $this->cashierId;
    }
}
