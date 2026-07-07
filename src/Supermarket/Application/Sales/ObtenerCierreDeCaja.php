<?php

declare(strict_types=1);

namespace Supermarket\Application\Sales;

use Supermarket\Domain\Sales\CashClose;
use Supermarket\Domain\Sales\SaleRepository;

/**
 * Use case: ObtenerCierreDeCaja.
 *
 * Builds the cash-close report (cierre de caja) for a cashier on a given day,
 * filtering to confirmed sales only.
 */
final class ObtenerCierreDeCaja
{
    public function __construct(
        private readonly SaleRepository $sales,
    ) {}

    public function execute(string $cashierId, \DateTimeImmutable $day): CashClose
    {
        return CashClose::forCashierOn($cashierId, $day, ...$this->sales->all());
    }
}
