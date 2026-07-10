<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

/**
 * The decision output of the replenishment policy for a single product:
 * how many units to move from the warehouse to the shelf, and whether a
 * low-stock alert should be emitted afterwards.
 */
final class DecisionDeReposicion
{
    public function __construct(
        private readonly string $productId,
        private readonly int $quantityToMove,
        private readonly bool $emitsAlert,
    ) {}

    public function productId(): string
    {
        return $this->productId;
    }

    public function quantityToMove(): int
    {
        return $this->quantityToMove;
    }

    public function emitsAlert(): bool
    {
        return $this->emitsAlert;
    }

    public function hasReplenishment(): bool
    {
        return $this->quantityToMove > 0;
    }
}
