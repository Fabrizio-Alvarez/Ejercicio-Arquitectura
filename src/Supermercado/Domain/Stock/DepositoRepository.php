<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

interface DepositoRepository
{
    public function find(string $productId): ?Deposito;

    public function save(Deposito $warehouse): void;

    /**
     * @return Deposito[]
     */
    public function all(): array;
}
