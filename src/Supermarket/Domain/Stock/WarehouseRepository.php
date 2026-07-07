<?php

declare(strict_types=1);

namespace Supermarket\Domain\Stock;

interface WarehouseRepository
{
    public function find(string $productId): ?Warehouse;

    public function save(Warehouse $warehouse): void;

    /**
     * @return Warehouse[]
     */
    public function all(): array;
}
