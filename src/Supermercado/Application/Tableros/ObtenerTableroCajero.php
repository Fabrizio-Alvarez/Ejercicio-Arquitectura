<?php

declare(strict_types=1);

namespace Supermercado\Application\Tableros;

use Supermercado\Domain\Comun\Clock;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\VentaRepository;

/**
 * Caso de uso: ObtenerTableroCajero.
 *
 * Consolida los KPIs de ventas del día para el tablero del cajero:
 * total vendido, cantidad de ventas, ticket promedio y desglose por
 * método de pago. Filtra en memoria las ventas confirmadas del día.
 */
final class ObtenerTableroCajero
{
    public function __construct(
        private readonly VentaRepository $ventas,
        private readonly Clock $reloj,
    ) {}

    public function execute(): TableroCajeroView
    {
        $hoy = $this->reloj->now();

        $delDia = array_filter(
            $this->ventas->all(),
            static fn (Venta $v): bool => $v->isConfirmed() && $v->isOnDay($hoy),
        );

        // Más reciente primero (para últimas ventas)
        usort($delDia, fn (Venta $a, Venta $b): int => $b->createdAt() <=> $a->createdAt());

        $totalCentavos = 0;
        $moneda = 'ARS';
        $porMetodo = [];

        foreach ($delDia as $v) {
            $total = $v->total();
            $totalCentavos += $total->amount();
            $moneda = $total->currency();
            $metodo = $v->metodoDePago()->value;
            $porMetodo[$metodo] = ($porMetodo[$metodo] ?? 0) + $total->amount();
        }

        $cantidad = count($delDia);

        $ultimas = array_slice(
            array_map(static fn (Venta $v): array => [
                'id' => $v->id(),
                'cliente' => $v->customerName(),
                'total' => $v->total()->amount(),
                'moneda' => $v->total()->currency(),
                'metodo' => $v->metodoDePago()->value,
                'hora' => $v->createdAt()->format('H:i:s'),
            ], $delDia),
            0,
            5,
        );

        return new TableroCajeroView(
            totalVentas: $totalCentavos,
            moneda: $moneda,
            cantidadVentas: $cantidad,
            ticketPromedio: $cantidad > 0 ? intdiv($totalCentavos, $cantidad) : 0,
            desglosePorMetodo: $porMetodo,
            ultimasVentas: $ultimas,
        );
    }
}
