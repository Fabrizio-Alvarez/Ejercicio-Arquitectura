<?php

declare(strict_types=1);

namespace Supermercado\Domain\Catalogo;

/**
 * Repository port (hexagonal): the domain defines the contract, the
 * infrastructure provides the Eloquent adapter. Use cases depend on this
 * interface, never on Eloquent.
 */
interface ProductoRepository
{
    public function find(string $id): ?Producto;

    public function save(Producto $product): void;

    /**
     * @return Producto[]
     */
    public function all(): array;
}
