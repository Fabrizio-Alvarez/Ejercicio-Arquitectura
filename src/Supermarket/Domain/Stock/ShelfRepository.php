<?php

declare(strict_types=1);

namespace Supermarket\Domain\Stock;

interface ShelfRepository
{
    public function find(string $productId): ?Shelf;

    public function save(Shelf $shelf): void;

    /**
     * @return Shelf[]
     */
    public function all(): array;
}
