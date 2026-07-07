<?php

declare(strict_types=1);

namespace Supermarket\Domain\Stock;

/**
 * Warehouse (almacén): the backstock used to replenish the shelves.
 */
final class Warehouse
{
    public function __construct(
        private readonly string $productId,
        private int $quantity,
    ) {
        if ($quantity < 0) {
            throw new \InvalidArgumentException("Warehouse quantity cannot be negative for product {$this->productId}.");
        }
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function isLow(): bool
    {
        return $this->quantity < ReplenishmentPolicy::LOW_WAREHOUSE_THRESHOLD;
    }

    public function take(int $amount): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Warehouse take amount cannot be negative.');
        }

        if ($amount > $this->quantity) {
            throw new \DomainException("Insufficient warehouse stock for product {$this->productId}: requested {$amount}, have {$this->quantity}.");
        }

        $this->quantity -= $amount;
    }
}
