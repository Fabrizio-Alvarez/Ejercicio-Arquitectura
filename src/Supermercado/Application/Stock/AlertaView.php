<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

use Supermercado\Domain\Stock\AlertaDeStock;

/**
 * Read model: la vista de una alerta de stock bajo persistida.
 */
final class AlertaView
{
    public function __construct(
        public readonly string $productoId,
        public readonly string $ubicacion,
        public readonly int $cantidad,
        public readonly string $fecha,
    ) {}

    public static function from(AlertaDeStock $a): self
    {
        return new self(
            productoId: $a->productId(),
            ubicacion: $a->ubicacion()->value,
            cantidad: $a->cantidad(),
            fecha: $a->at()->format('Y-m-d H:i:s'),
        );
    }
}
