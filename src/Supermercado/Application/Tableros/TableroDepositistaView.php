<?php

declare(strict_types=1);

namespace Supermercado\Application\Tableros;

use Supermercado\Application\Stock\AlertaView;
use Supermercado\Application\Stock\MovimientoView;

/**
 * Read model: tablero del depositista.
 *
 * KPIs de alertas y movimientos del depósito.
 *
 * @phpstan-import-type AlertaViewData from AlertaView
 * @phpstan-import-type MovimientoViewData from MovimientoView
 */
final class TableroDepositistaView
{
    /**
     * @param  AlertaView[]  $alertas
     * @param  MovimientoView[]  $movimientosRecientes
     */
    public function __construct(
        public readonly int $alertasActivas,
        public readonly int $alertasDeposito,
        public readonly int $alertasGondola,
        public readonly int $reabastecimientosHoy,
        public readonly array $alertas,
        public readonly array $movimientosRecientes,
    ) {}
}
