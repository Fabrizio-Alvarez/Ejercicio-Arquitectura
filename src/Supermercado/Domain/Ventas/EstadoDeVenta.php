<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

enum EstadoDeVenta: string
{
    case Pendiente = 'pendiente';
    case EsperandoPago = 'esperando_pago';
    case Confirmada = 'confirmada';
    case Cancelada = 'cancelada';
}
