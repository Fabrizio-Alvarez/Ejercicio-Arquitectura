<?php

declare(strict_types=1);

namespace Supermercado\Application\Ventas;

final class VentaNoEncontradaException extends \DomainException
{
    public static function forId(string $ventaId): self
    {
        return new self("Venta not found: {$ventaId}.");
    }
}
