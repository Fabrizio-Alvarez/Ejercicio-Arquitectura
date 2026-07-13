<?php

declare(strict_types=1);

namespace Supermercado\Application\Tableros;

use Supermercado\Application\Stock\VistaDeStock;

/**
 * Read model: tablero del repositor.
 *
 * KPIs de stock crítico: productos con góndola o depósito bajo.
 */
final class TableroRepositorView
{
    /**
     * @param  VistaDeStock[]  $stockCritico  solo los productos con algún flag de bajo
     */
    public function __construct(
        public readonly int $productosGondolaBaja,
        public readonly int $productosDepositoBajo,
        public readonly int $totalProductos,
        public readonly array $stockCritico,
    ) {}
}
