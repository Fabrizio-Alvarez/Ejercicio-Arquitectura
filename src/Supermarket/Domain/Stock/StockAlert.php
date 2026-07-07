<?php

declare(strict_types=1);

namespace Supermarket\Domain\Stock;

/**
 * A low-stock alert: emitted by the replenishment flow when the warehouse
 * for a product drops below its threshold.
 */
final class StockAlert
{
    public function __construct(
        private readonly string $productId,
        private readonly int $warehouseQuantity,
        private readonly \DateTimeImmutable $at,
    ) {}

    public function productId(): string
    {
        return $this->productId;
    }

    public function warehouseQuantity(): int
    {
        return $this->warehouseQuantity;
    }

    public function at(): \DateTimeImmutable
    {
        return $this->at;
    }
}
