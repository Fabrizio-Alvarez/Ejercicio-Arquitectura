<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\DepositoRepository;

/**
 * Use case: ListarStock.
 *
 * Returns the current stock view across all products (shelf + warehouse
 * quantities and their low-stock flags).
 *
 * @return VistaDeStock[]
 */
final class ListarStock
{
    public function __construct(
        private readonly GondolaRepository $shelves,
        private readonly DepositoRepository $warehouses,
    ) {}

    public function execute(): array
    {
        $productIds = array_unique(array_merge(
            array_map(fn (Gondola $shelf) => $shelf->productId(), $this->shelves->all()),
            array_map(fn (\Supermercado\Domain\Stock\Deposito $warehouse) => $warehouse->productId(), $this->warehouses->all()),
        ));

        $views = [];

        foreach ($productIds as $productId) {
            $shelf = $this->shelves->find($productId);
            $warehouse = $this->warehouses->find($productId);

            $views[] = new VistaDeStock(
                productId: $productId,
                shelfQuantity: $shelf?->quantity() ?? 0,
                warehouseQuantity: $warehouse?->quantity() ?? 0,
                shelfLow: $shelf !== null && $shelf->isLow(),
                warehouseLow: $warehouse !== null && $warehouse->isLow(),
            );
        }

        return $views;
    }
}
