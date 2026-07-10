<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

/**
 * Movimiento de stock: un registro de auditoría del depósito.
 *
 * Cada vez que el stock de un producto cambia por una causa relevante (una
 * venta, una reposición, un ajuste), se registra un movimiento. Esto es la
 * "voz" del depósito: "el depósito avisa a su repositorio" dejando huella de
 * lo que ocurre con el inventario.
 */
final class MovimientoDeStock
{
    public function __construct(
        private readonly string $id,
        private readonly string $productoId,
        private readonly TipoDeMovimiento $tipo,
        private readonly int $cantidad,
        private readonly UbicacionDeStock $ubicacion,
        private readonly \DateTimeImmutable $fecha,
        private readonly ?string $referencia = null,
    ) {
        if ($cantidad < 0) {
            throw new \InvalidArgumentException("La cantidad del movimiento no puede ser negativa para el producto {$this->productoId}.");
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function productoId(): string
    {
        return $this->productoId;
    }

    public function tipo(): TipoDeMovimiento
    {
        return $this->tipo;
    }

    public function cantidad(): int
    {
        return $this->cantidad;
    }

    public function ubicacion(): UbicacionDeStock
    {
        return $this->ubicacion;
    }

    public function fecha(): \DateTimeImmutable
    {
        return $this->fecha;
    }

    public function referencia(): ?string
    {
        return $this->referencia;
    }
}
