<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Payments;

use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Ventas\MetodoDePago;
use Supermercado\Domain\Ventas\PaymentGateway;
use Supermercado\Domain\Ventas\ResultadoDePago;

/**
 * Adapter mock de PaymentGateway: siempre aprueba.
 *
 * No hay integración de pago real todavía. Este adapter permite que el flujo
 * completo (cobrar → pagar → confirmar) funcione end-to-end. Cuando se integre
 * un proveedor real, se reemplaza el binding en AppServiceProvider.
 */
final class AlwaysSucceedsPaymentGateway implements PaymentGateway
{
    public function charge(Dinero $monto, MetodoDePago $metodo): ResultadoDePago
    {
        return ResultadoDePago::exitoso('mock-' . uniqid());
    }
}
