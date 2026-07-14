<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

/**
 * Deposito (almacén): the backstock used to replenish the shelves.
 */
final class Deposito
{
    /** Umbral a partir del cual el depósito se considera con stock bajo. */
    public const UMBRAL_BAJO = 150;

    public function __construct(
        private readonly string $productId,
        private int $quantity,
        private int $umbralBajo = self::UMBRAL_BAJO,
    ) {
        if ($quantity < 0) {
            throw new \InvalidArgumentException("Deposito quantity cannot be negative for product {$this->productId}.");
        }
        if ($umbralBajo < 0) {
            throw new \InvalidArgumentException("Umbral bajo cannot be negative for product {$this->productId}.");
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
        return $this->quantity < $this->umbralBajo;
    }

    public function umbralBajo(): int
    {
        return $this->umbralBajo;
    }

    public function configurarUmbral(int $umbral): void
    {
        if ($umbral < 0) {
            throw new \InvalidArgumentException("Umbral bajo cannot be negative for product {$this->productId}.");
        }
        $this->umbralBajo = $umbral;
    }

    /**
     * Cuántas unidades como máximo puede entregar el depósito ante un pedido,
     * sin exceder el stock disponible. Nunca negativo.
     */
    public function maxAvailableFor(int $requested): int
    {
        if ($requested < 0) {
            throw new \InvalidArgumentException('El pedido al depósito no puede ser negativo.');
        }

        return min($requested, $this->quantity);
    }

    /**
     * Proyección SIN mutar: quedaría el depósito por debajo de su umbral tras
     * extraer estas unidades. La política lo usa para decidir la alerta antes
     * de tocar el estado.
     */
    public function wouldBeLowAfter(int $toTake): bool
    {
        if ($toTake < 0) {
            throw new \InvalidArgumentException('La cantidad a extraer no puede ser negativa.');
        }

        return ($this->quantity - $toTake) < $this->umbralBajo;
    }

    public function take(int $amount): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Deposito take amount cannot be negative.');
        }

        if ($amount > $this->quantity) {
            throw new \DomainException("Insufficient warehouse stock for product {$this->productId}: requested {$amount}, have {$this->quantity}.");
        }

        $this->quantity -= $amount;
    }

    /**
     * Recibe stock del proveedor (reabastecimiento del depósito).
     */
    public function receive(int $amount): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Deposito receive amount cannot be negative.');
        }

        $this->quantity += $amount;
    }
}
