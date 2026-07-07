<?php

declare(strict_types=1);

namespace Supermarket\Application\Stock;

use Supermarket\Domain\Stock\Shelf;
use Supermarket\Domain\Stock\ShelfRepository;
use Supermarket\Domain\Stock\WarehouseRepository;

/**
 * Use case: ListarStock.
 *
 * Returns the current stock view across all products (shelf + warehouse
 * quantities and their low-stock flags).
 *
 * @return StockView[]
 */
final class ListarStock
{
    public function __construct(
        private readonly ShelfRepository $shelves,
        private readonly WarehouseRepository $warehouses,
    ) {}

    public function execute(): array
    {
        $productIds = array_unique(array_merge(
            array_map(fn (Shelf $shelf) => $shelf->productId(), $this->shelves->all()),
            array_map(fn (\Supermarket\Domain\Stock\Warehouse $warehouse) => $warehouse->productId(), $this->warehouses->all()),
        ));

        $views = [];

        foreach ($productIds as $productId) {
            $shelf = $this->shelves->find($productId);
            $warehouse = $this->warehouses->find($productId);

            $views[] = new StockView(
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
