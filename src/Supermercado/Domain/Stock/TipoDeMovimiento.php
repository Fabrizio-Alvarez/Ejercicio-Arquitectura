<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

/**
 * Tipo de movimiento de stock registrado en el depósito (auditoría).
 */
enum TipoDeMovimiento: string
{
    case Venta = 'venta';
    case Reposicion = 'reposicion';
    case Ajuste = 'ajuste';
    case Reabastecimiento = 'reabastecimiento';
    case Devolucion = 'devolucion';
}
