<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

/**
 * Método de pago: cómo registró el pago el cliente en la venta.
 * Value object inmutable (enum tipado).
 */
enum MetodoDePago: string
{
    case Efectivo = 'efectivo';
    case TarjetaCredito = 'tarjeta_credito';
    case TarjetaDebito = 'tarjeta_debito';
    case Transferencia = 'transferencia';
    case QR = 'qr';
}
