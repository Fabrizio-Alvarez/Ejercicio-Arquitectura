<?php

declare(strict_types=1);

namespace Supermarket\Domain\Catalog;

use Supermarket\Domain\Shared\Money;

/**
 * Offer: a percentage discount on a product, valid only during a time window.
 * Immutable value object.
 */
final class Offer
{
    public function __construct(
        private readonly string $productId,
        private readonly float $percent,
        private readonly \DateTimeImmutable $validFrom,
        private readonly \DateTimeImmutable $validTo,
    ) {
        if ($percent < 0 || $percent > 100) {
            throw new \InvalidArgumentException("Offer percent must be between 0 and 100, got {$percent}.");
        }

        if ($validTo < $validFrom) {
            throw new \InvalidArgumentException('Offer validTo cannot be earlier than validFrom.');
        }
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function percent(): float
    {
        return $this->percent;
    }

    public function validFrom(): \DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function validTo(): \DateTimeImmutable
    {
        return $this->validTo;
    }

    /** True when the offer is in effect at the given instant. */
    public function isActive(\DateTimeImmutable $at): bool
    {
        return $at >= $this->validFrom && $at <= $this->validTo;
    }

    public function covers(string $productId): bool
    {
        return $this->productId === $productId;
    }

    /** Apply this offer's discount to a price, returning the discounted price. */
    public function applyTo(Money $price): Money
    {
        return $price->applyPercent($this->percent);
    }
}
