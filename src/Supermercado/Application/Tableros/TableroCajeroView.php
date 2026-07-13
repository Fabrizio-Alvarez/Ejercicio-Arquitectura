<?php

declare(strict_types=1);

namespace Supermercado\Application\Tableros;

/**
 * Read model: tablero del cajero.
 *
 * KPIs de ventas del día: total, cantidad, ticket promedio, desglose por
 * método de pago y últimas ventas.
 */
final class TableroCajeroView
{
    /**
     * @param  array<string, int>  $desglosePorMetodo  metodo => total en centavos
     * @param  array<int, array{id:string, cliente:string, total:int, moneda:string, metodo:string, hora:string}>  $ultimasVentas
     */
    public function __construct(
        public readonly int $totalVentas,
        public readonly string $moneda,
        public readonly int $cantidadVentas,
        public readonly int $ticketPromedio,
        public readonly array $desglosePorMetodo,
        public readonly array $ultimasVentas,
    ) {}
}
