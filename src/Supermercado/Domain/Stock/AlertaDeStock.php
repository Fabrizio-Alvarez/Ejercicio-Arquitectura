<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

/**
 * Alerta de stock bajo: se emite cuando el stock de un producto en una ubicación
 * (góndola o depósito) cae por debajo de su umbral.
 *
 * Funciona tanto como valor de retorno del flujo de reposición (cuando el
 * depósito cae bajo 150) como evento de dominio despachado cuando una compra
 * deja la góndola por debajo de su mínimo.
 */
final class AlertaDeStock
{
    public function __construct(
        private readonly string $productId,
        private readonly UbicacionDeStock $ubicacion,
        private readonly int $cantidad,
        private readonly \DateTimeImmutable $at,
    ) {}

    public function productId(): string
    {
        return $this->productId;
    }

    public function ubicacion(): UbicacionDeStock
    {
        return $this->ubicacion;
    }

    public function cantidad(): int
    {
        return $this->cantidad;
    }

    public function at(): \DateTimeImmutable
    {
        return $this->at;
    }
}
