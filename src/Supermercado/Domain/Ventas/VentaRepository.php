<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

interface VentaRepository
{
    public function find(string $id): ?Venta;

    public function save(Venta $sale): void;

    /**
     * @return Venta[]
     */
    public function all(): array;
}
