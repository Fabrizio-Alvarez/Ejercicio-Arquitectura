<?php

declare(strict_types=1);

namespace Supermercado\Application\Reportes;

use Supermercado\Domain\Stock\MovimientoDeStockRepository;

/**
 * Use case: ObtenerReporteMovimientos.
 *
 * Agrega en memoria los movimientos de stock por tipo (venta, reposición,
 * ajuste, reabastecimiento): cantidad de registros y total de unidades.
 */
final class ObtenerReporteMovimientos
{
    public function __construct(
        private readonly MovimientoDeStockRepository $movimientos,
    ) {}

    public function execute(): ReporteMovimientosView
    {
        $porTipo = [];

        foreach ($this->movimientos->all() as $mov) {
            $tipo = $mov->tipo()->value;
            $porTipo[$tipo] ??= ['tipo' => $tipo, 'cantidad' => 0, 'unidades' => 0];
            $porTipo[$tipo]['cantidad']++;
            $porTipo[$tipo]['unidades'] += $mov->cantidad();
        }

        return new ReporteMovimientosView(
            movimientosPorTipo: array_values($porTipo),
        );
    }
}
