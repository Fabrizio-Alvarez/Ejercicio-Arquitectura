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
        private int $umbralBajo = self::UMBRAL_BAJO,
        private int $reservado = 0,
    ) {
        if ($quantity < 0) {
            throw new \InvalidArgumentException("Gondola quantity cannot be negative for product {$this->productId}.");
        }
        if ($umbralBajo < 0) {
            throw new \InvalidArgumentException("Umbral bajo cannot be negative for product {$this->productId}.");
        }
        if ($reservado < 0 || $reservado > $quantity) {
            throw new \InvalidArgumentException("Reservado debe estar entre 0 y quantity para el producto {$this->productId}.");
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

    public function reservado(): int
    {
        return $this->reservado;
    }

    /** Stock disponible para nuevas ventas = quantity − reservado. */
    public function disponible(): int
    {
        return $this->quantity - $this->reservado;
    }

    /**
     * Reserva unidades para una venta en EsperandoPago. Lanza si no hay
     * stock disponible (quantity − reservado < cantidad).
     */
    public function reservar(int $cantidad): void
    {
        if ($cantidad < 1) {
            throw new \InvalidArgumentException('La cantidad a reservar debe ser >= 1.');
        }

        if ($cantidad > $this->disponible()) {
            throw new \DomainException("Stock insuficiente en góndola para reservar del producto {$this->productId}: solicitado {$cantidad}, disponible {$this->disponible()}.");
        }

        $this->reservado += $cantidad;
    }

    /**
     * Convierte una reserva en deducción real: libera la reserva y descuenta
     * el quantity. Compatible con ventas sin reserva previa (reservado=0):
     * en ese caso equivale a descontar().
     */
    public function confirmarReserva(int $cantidad): void
    {
        if ($cantidad < 1) {
            throw new \InvalidArgumentException('La cantidad a confirmar debe ser >= 1.');
        }

        if ($cantidad > $this->quantity) {
            throw new \DomainException("Stock insuficiente en góndola para el producto {$this->productId}: solicitado {$cantidad}, disponible {$this->quantity}.");
        }

        $this->reservado = max(0, $this->reservado - $cantidad);
        $this->quantity -= $cantidad;
    }

    /** Libera una reserva sin descontar stock (pago rechazado, cancelación). */
    public function liberarReserva(int $cantidad): void
    {
        if ($cantidad < 1) {
            throw new \InvalidArgumentException('La cantidad a liberar debe ser >= 1.');
        }

        $this->reservado = max(0, $this->reservado - $cantidad);
    }
}
