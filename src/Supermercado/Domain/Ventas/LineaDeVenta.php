<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

use Supermercado\Domain\Comun\Dinero;

/**
 * A priced line within a sale: a product, a quantity and the (possibly
 * discounted) unit price applied at the time of sale. The unit price is
 * frozen here — sale totals are immutable once registered.
 */
final class LineaDeVenta
{
    public function __construct(
        private readonly string $productId,
        private readonly string $productName,
        private readonly int $quantity,
        private readonly Dinero $unitPrice,
    ) {
        if ($quantity < 1) {
            throw new \InvalidArgumentException("Venta line quantity must be >= 1, got {$quantity}.");
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

    public function unitPrice(): Dinero
    {
        return $this->unitPrice;
    }

    public function total(): Dinero
    {
        return $this->unitPrice->multiply($this->quantity);
    }

    public function currency(): string
    {
        return $this->unitPrice->currency();
    }
}
