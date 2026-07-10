<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

/**
 * Read model: the stock view of a single product (shelf + warehouse).
 */
final class VistaDeStock
{
    public function __construct(
        public readonly string $productId,
        public readonly int $shelfQuantity,
        public readonly int $warehouseQuantity,
        public readonly bool $shelfLow,
        public readonly bool $warehouseLow,
    ) {}
}
