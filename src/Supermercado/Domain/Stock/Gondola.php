<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

/**
 * Gondola (góndola): the quantity of a product on display.
 */
final class Gondola
{
    /** Umbral a partir del cual la góndola se considera con stock bajo. */
    public const UMBRAL_BAJO = 30;

    public function __construct(
        private readonly string $productId,
        private int $quantity,
    ) {
        if ($quantity < 0) {
            throw new \InvalidArgumentException("Gondola quantity cannot be negative for product {$this->productId}.");
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
        return $this->quantity < self::UMBRAL_BAJO;
    }

    /**
     * Unidades que faltan en la góndola para alcanzar un nivel objetivo.
     * Nunca negativo: si ya está en o por encima del objetivo, no hace falta
     * reponer nada. Es la pregunta que la política de reposición le hace a la
     * góndola en lugar de calcularlo afuera ("Tell, Don't Ask").
     */
    public function gapTo(int $target): int
    {
        return max(0, $target - $this->quantity);
    }

    public function restock(int $amount): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Restock amount cannot be negative.');
        }

        $this->quantity += $amount;
    }

    /**
     * Descuenta unidades vendidas de la góndola. Lanza si no hay stock
     * suficiente: no se puede vender lo que no está exhibido.
     */
    public function descontar(int $cantidad): void
    {
        if ($cantidad < 0) {
            throw new \InvalidArgumentException('La cantidad a descontar no puede ser negativa.');
        }

        if ($cantidad > $this->quantity) {
            throw new \DomainException("Stock insuficiente en góndola para el producto {$this->productId}: solicitado {$cantidad}, disponible {$this->quantity}.");
        }

        $this->quantity -= $cantidad;
    }
}
