<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

/**
 * Repository port (hexagonal) para los movimientos de stock del depósito.
 * El dominio define el contrato; la infraestructura provee el adapter Eloquent.
 */
interface MovimientoDeStockRepository
{
    public function save(MovimientoDeStock $movimiento): void;

    /**
     * @return MovimientoDeStock[]
     */
    public function all(): array;

    /**
     * @return MovimientoDeStock[]
     */
    public function findByProducto(string $productoId): array;
}
