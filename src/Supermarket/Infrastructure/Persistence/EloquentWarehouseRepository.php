<?php

declare(strict_types=1);

namespace Supermarket\Infrastructure\Persistence;

use Supermarket\Domain\Stock\Warehouse;
use Supermarket\Domain\Stock\WarehouseRepository;

final class EloquentWarehouseRepository implements WarehouseRepository
{
    public function find(string $productId): ?Warehouse
    {
        $row = WarehouseModel::find($productId);

        return $row !== null ? $this->toDomain($row) : null;
    }

    public function save(Warehouse $warehouse): void
    {
        WarehouseModel::updateOrCreate(
            ['product_id' => $warehouse->productId()],
            ['quantity' => $warehouse->quantity()],
        );
    }

    public function all(): array
    {
        return WarehouseModel::all()
            ->map(fn (WarehouseModel $row) => $this->toDomain($row))
            ->all();
    }

    private function toDomain(WarehouseModel $row): Warehouse
    {
        return new Warehouse((string) $row->product_id, (int) $row->quantity);
    }
}
