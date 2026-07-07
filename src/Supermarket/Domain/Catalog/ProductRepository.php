<?php

declare(strict_types=1);

namespace Supermarket\Domain\Catalog;

/**
 * Repository port (hexagonal): the domain defines the contract, the
 * infrastructure provides the Eloquent adapter. Use cases depend on this
 * interface, never on Eloquent.
 */
interface ProductRepository
{
    public function find(string $id): ?Product;

    public function save(Product $product): void;

    /**
     * @return Product[]
     */
    public function all(): array;
}
