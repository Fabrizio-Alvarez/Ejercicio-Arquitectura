<?php

declare(strict_types=1);

namespace Supermercado\Application\Reportes;

/**
 * DTO: reporte histórico de ventas.
 *
 * @param array<int, array{fecha:string, total:float, cantidad:int}> $ventasPorDia
 * @param array<int, array{productoId:string, unidades:int, total:float}> $topProductos
 */
final class ReporteVentasView
{
    public function __construct(
        public readonly array $ventasPorDia,
        public readonly float $totalGeneral,
        public readonly int $cantidadVentas,
        public readonly float $ticketPromedio,
        public readonly array $topProductos,
    ) {}
}
