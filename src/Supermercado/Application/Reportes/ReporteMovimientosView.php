<?php

declare(strict_types=1);

namespace Supermercado\Application\Reportes;

/**
 * DTO: reporte histórico de movimientos de stock.
 *
 * @param array<int, array{tipo:string, cantidad:int, unidades:int}> $movimientosPorTipo
 */
final class ReporteMovimientosView
{
    public function __construct(
        public readonly array $movimientosPorTipo,
    ) {}
}
