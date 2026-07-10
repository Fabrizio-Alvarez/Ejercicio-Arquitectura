<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

use Supermercado\Domain\Comun\Dinero;

/**
 * One row of a cash close (cierre de caja): a single confirmed sale,
 * with the customer, the amount+currency charged, and the cashier.
 */
final class ResumenDeVenta
{
    public function __construct(
        private readonly string $saleId,
        private readonly string $customerName,
        private readonly Dinero $amount,
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

    public function amount(): Dinero
    {
        return $this->amount;
    }

    public function cashierId(): string
    {
        return $this->cashierId;
    }
}
