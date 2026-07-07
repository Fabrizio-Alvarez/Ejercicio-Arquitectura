<?php

declare(strict_types=1);

namespace Supermarket\Domain\Sales;

use Supermarket\Domain\Shared\Money;

/**
 * A priced line within a sale: a product, a quantity and the (possibly
 * discounted) unit price applied at the time of sale. The unit price is
 * frozen here — sale totals are immutable once registered.
 */
final class SaleLine
{
    public function __construct(
        private readonly string $productId,
        private readonly string $productName,
        private readonly int $quantity,
        private readonly Money $unitPrice,
    ) {
        if ($quantity < 1) {
            throw new \InvalidArgumentException("Sale line quantity must be >= 1, got {$quantity}.");
        }
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function total(): Money
    {
        return $this->unitPrice->multiply($this->quantity);
    }

    public function currency(): string
    {
        return $this->unitPrice->currency();
    }
}
