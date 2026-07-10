<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

interface GondolaRepository
{
    public function find(string $productId): ?Gondola;

    public function save(Gondola $shelf): void;

    /**
     * @return Gondola[]
     */
    public function all(): array;
}
