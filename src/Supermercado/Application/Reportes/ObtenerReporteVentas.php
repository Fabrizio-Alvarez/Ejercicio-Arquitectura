<?php

declare(strict_types=1);

namespace Supermercado\Application\Reportes;

use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\VentaRepository;

/**
 * Use case: ObtenerReporteVentas.
 *
 * Agrega en memoria las ventas confirmadas del histórico: total/cantidad por
 * día, ticket promedio y top 10 productos por unidades vendidas. Los montos
 * se convierten a decimales (el dominio trabaja en centavos) para que la vista
 * los consuma directamente.
 */
final class ObtenerReporteVentas
{
    public function __construct(
        private readonly VentaRepository $ventas,
    ) {}

    public function execute(): ReporteVentasView
    {
        $confirmadas = array_filter(
            $this->ventas->all(),
            static fn (Venta $v) => $v->isConfirmed(),
        );

        /** @var array<string, array{fecha:string, total:int, cantidad:int}> $porDia */
        $porDia = [];
        $totalCentavos = 0;
        $cantidad = 0;
        /** @var array<string, array{productoId:string, unidades:int, total:int}> $productos */
        $productos = [];

        foreach ($confirmadas as $venta) {
            $fecha = $venta->createdAt()->format('Y-m-d');
            $total = $venta->total()->amount();

            $porDia[$fecha] ??= ['fecha' => $fecha, 'total' => 0, 'cantidad' => 0];
            $porDia[$fecha]['total'] += $total;
            $porDia[$fecha]['cantidad']++;

            $totalCentavos += $total;
            $cantidad++;

            foreach ($venta->lines() as $linea) {
                $pid = $linea->productId();
                $productos[$pid] ??= ['productoId' => $pid, 'unidades' => 0, 'total' => 0];
                $productos[$pid]['unidades'] += $linea->quantity();
                $productos[$pid]['total'] += $linea->total()->amount();
            }
        }

        \ksort($porDia);

        \usort($productos, static fn ($a, $b) => $b['unidades'] <=> $a['unidades']);

        return new ReporteVentasView(
            ventasPorDia: array_map(static fn ($d) => [
                'fecha' => $d['fecha'],
                'total' => (float) ($d['total'] / 100),
                'cantidad' => $d['cantidad'],
            ], array_values($porDia)),
            totalGeneral: $totalCentavos / 100,
            cantidadVentas: $cantidad,
            ticketPromedio: $cantidad > 0 ? ($totalCentavos / $cantidad) / 100 : 0.0,
            topProductos: array_map(static fn ($p) => [
                'productoId' => $p['productoId'],
                'unidades' => $p['unidades'],
                'total' => (float) ($p['total'] / 100),
            ], array_slice($productos, 0, 10)),
        );
    }
}
