<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

use Supermercado\Domain\Comun\Dinero;

/**
 * Port: procesa el cobro de una venta.
 *
 * El dominio define la interfaz; la infraestructura proporciona el adapter.
 * Igual que Clock, es un punto de inyección que mantiene al dominio puro:
 * CobrarProductos depende de esta interfaz, no de Stripe/MercadoPago/whatsoever.
 */
interface PaymentGateway
{
    public function charge(Dinero $monto, MetodoDePago $metodo): ResultadoDePago;
}
