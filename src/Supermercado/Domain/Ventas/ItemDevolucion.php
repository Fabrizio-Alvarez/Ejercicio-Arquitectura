<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

/**
 * Value object: una línea de devolución dentro de una devolución.
 *
 * Indica qué producto y cuántas unidades se devuelven de una venta confirmada.
 */
final class ItemDevolucion
{
    public function __construct(
        private readonly string $productoId,
        private readonly int $cantidad,
    ) {
        if ($cantidad < 1) {
            throw new \InvalidArgumentException("La cantidad a devolver debe ser >= 1, got {$cantidad}.");
        }
    }

    public function productoId(): string
    {
        return $this->productoId;
    }

    public function cantidad(): int
    {
        return $this->cantidad;
    }
}
