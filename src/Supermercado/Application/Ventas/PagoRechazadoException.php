<?php

declare(strict_types=1);

namespace Supermercado\Application\Ventas;

/**
 * Se lanza cuando el PaymentGateway rechaza el pago de una venta.
 * La venta se persiste como Cancelada antes de lanzar.
 */
final class PagoRechazadoException extends \RuntimeException
{
    public static function forSale(string $saleId): self
    {
        return new self("El pago fue rechazado para la venta {$saleId}.");
    }
}
