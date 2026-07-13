<?php

declare(strict_types=1);

namespace Supermercado\Application\Tableros;

use Supermercado\Application\Stock\AlertaView;
use Supermercado\Application\Stock\MovimientoView;
use Supermercado\Domain\Comun\Clock;
use Supermercado\Domain\Stock\AlertaDeStockRepository;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;

/**
 * Caso de uso: ObtenerTableroDepositista.
 *
 * Consolida los KPIs del depósito para el tablero del depositista:
 * alertas activas (góndola + depósito), reabastecimientos del día y
 * los movimientos y alertas más recientes.
 */
final class ObtenerTableroDepositista
{
    public function __construct(
        private readonly AlertaDeStockRepository $alertas,
        private readonly MovimientoDeStockRepository $movimientos,
        private readonly Clock $reloj,
    ) {}

    public function execute(): TableroDepositistaView
    {
        $hoy = $this->reloj->now()->format('Y-m-d');

        $alertasRaw = $this->alertas->all();
        $alertasDeposito = 0;
        $alertasGondola = 0;

        foreach ($alertasRaw as $a) {
            if ($a->ubicacion() === UbicacionDeStock::Deposito) {
                $alertasDeposito++;
            } else {
                $alertasGondola++;
            }
        }

        $movimientosRaw = $this->movimientos->all();

        $reabastecimientosHoy = 0;
        foreach ($movimientosRaw as $m) {
            if ($m->tipo() === TipoDeMovimiento::Reabastecimiento
                && $m->fecha()->format('Y-m-d') === $hoy) {
                $reabastecimientosHoy++;
            }
        }

        return new TableroDepositistaView(
            alertasActivas: count($alertasRaw),
            alertasDeposito: $alertasDeposito,
            alertasGondola: $alertasGondola,
            reabastecimientosHoy: $reabastecimientosHoy,
            alertas: array_slice(array_map(AlertaView::from(...), $alertasRaw), 0, 5),
            movimientosRecientes: array_slice(array_map(MovimientoView::from(...), $movimientosRaw), 0, 5),
        );
    }
}
