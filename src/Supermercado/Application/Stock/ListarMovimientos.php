<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

use Supermercado\Domain\Stock\MovimientoDeStockRepository;

/**
 * Caso de uso: ListarMovimientos.
 *
 * Devuelve la auditoría de movimientos de stock del depósito (ventas y
 * reposiciones), del más reciente al más viejo.
 *
 * @return MovimientoView[]
 */
final class ListarMovimientos
{
    public function __construct(
        private readonly MovimientoDeStockRepository $movimientos,
    ) {}

    public function execute(): array
    {
        return array_map(MovimientoView::from(...), $this->movimientos->all());
    }
}
