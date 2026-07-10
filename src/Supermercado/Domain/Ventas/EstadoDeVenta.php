<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

enum EstadoDeVenta: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Cancelada = 'cancelada';
}
