<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

/**
 * Resultado de un reabastecimiento del depósito: unidades recibidas y
 * nivel resultante del depósito para el producto.
 */
final class ResultadoDeReabastecimiento
{
    public function __construct(
        public readonly string $productId,
        public readonly int $recibido,
        public readonly int $nivelDelDeposito,
    ) {}
}
