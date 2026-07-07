<?php

declare(strict_types=1);

namespace Supermarket\Application\Stock;

/**
 * Read model: the stock view of a single product (shelf + warehouse).
 */
final class StockView
{
    public function __construct(
        public readonly string $productId,
        public readonly int $shelfQuantity,
        public readonly int $warehouseQuantity,
        public readonly bool $shelfLow,
        public readonly bool $warehouseLow,
    ) {}
}
