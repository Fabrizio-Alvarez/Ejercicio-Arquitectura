<?php

declare(strict_types=1);

namespace Supermarket\Domain\Stock;

/**
 * Shelf (góndola): the quantity of a product on display.
 */
final class Shelf
{
    public function __construct(
        private readonly string $productId,
        private int $quantity,
    ) {
        if ($quantity < 0) {
            throw new \InvalidArgumentException("Shelf quantity cannot be negative for product {$this->productId}.");
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
        return $this->quantity < ReplenishmentPolicy::LOW_SHELF_THRESHOLD;
    }

    public function restock(int $amount): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException("Restock amount cannot be negative.");
        }

        $this->quantity += $amount;
    }
}
