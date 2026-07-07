<?php

declare(strict_types=1);

namespace Supermarket\Domain\Sales;

interface SaleRepository
{
    public function find(string $id): ?Sale;

    public function save(Sale $sale): void;

    /**
     * @return Sale[]
     */
    public function all(): array;
}
