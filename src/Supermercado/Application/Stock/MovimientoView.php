<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

use Supermercado\Domain\Stock\MovimientoDeStock;

/**
 * Read model: la vista de un movimiento de stock del depósito.
 */
final class MovimientoView
{
    public function __construct(
        public readonly string $productoId,
        public readonly string $tipo,
        public readonly int $cantidad,
        public readonly string $ubicacion,
        public readonly ?string $referencia,
        public readonly string $fecha,
    ) {}

    public static function from(MovimientoDeStock $m): self
    {
        return new self(
            productoId: $m->productoId(),
            tipo: $m->tipo()->value,
            cantidad: $m->cantidad(),
            ubicacion: $m->ubicacion()->value,
            referencia: $m->referencia(),
            fecha: $m->fecha()->format('Y-m-d H:i:s'),
        );
    }
}
