<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;

final class EloquentDepositoRepository implements DepositoRepository
{
    public function find(string $productId): ?Deposito
    {
        $row = DepositoModel::find($productId);

        return $row !== null ? $this->toDomain($row) : null;
    }

    public function save(Deposito $warehouse): void
    {
        DepositoModel::updateOrCreate(
            ['product_id' => $warehouse->productId()],
            ['quantity' => $warehouse->quantity()],
        );
    }

    public function all(): array
    {
        return DepositoModel::all()
            ->map(fn (DepositoModel $row) => $this->toDomain($row))
            ->all();
    }

    private function toDomain(DepositoModel $row): Deposito
    {
        return new Deposito((string) $row->product_id, (int) $row->quantity);
    }
}
