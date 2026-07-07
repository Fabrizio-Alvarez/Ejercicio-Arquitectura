<?php

declare(strict_types=1);

namespace Supermarket\Infrastructure\Persistence;

use Supermarket\Domain\Stock\Shelf;
use Supermarket\Domain\Stock\ShelfRepository;

final class EloquentShelfRepository implements ShelfRepository
{
    public function find(string $productId): ?Shelf
    {
        $row = ShelfModel::find($productId);

        return $row !== null ? $this->toDomain($row) : null;
    }

    public function save(Shelf $shelf): void
    {
        ShelfModel::updateOrCreate(
            ['product_id' => $shelf->productId()],
            ['quantity' => $shelf->quantity()],
        );
    }

    public function all(): array
    {
        return ShelfModel::all()
            ->map(fn (ShelfModel $row) => $this->toDomain($row))
            ->all();
    }

    private function toDomain(ShelfModel $row): Shelf
    {
        return new Shelf((string) $row->product_id, (int) $row->quantity);
    }
}
