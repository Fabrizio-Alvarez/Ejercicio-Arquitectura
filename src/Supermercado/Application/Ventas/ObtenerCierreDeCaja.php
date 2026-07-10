<?php

declare(strict_types=1);

namespace Supermercado\Application\Ventas;

use Supermercado\Domain\Ventas\CierreDeCaja;
use Supermercado\Domain\Ventas\VentaRepository;

/**
 * Use case: ObtenerCierreDeCaja.
 *
 * Builds the cash-close report (cierre de caja) for a cashier on a given day,
 * filtering to confirmed sales only.
 */
final class ObtenerCierreDeCaja
{
    public function __construct(
        private readonly VentaRepository $sales,
    ) {}

    public function execute(string $cashierId, \DateTimeImmutable $day): CierreDeCaja
    {
        return CierreDeCaja::forCashierOn($cashierId, $day, ...$this->sales->all());
    }
}
