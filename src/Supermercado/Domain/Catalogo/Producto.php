<?php

declare(strict_types=1);

namespace Supermercado\Domain\Catalogo;

use Supermercado\Domain\Comun\Dinero;

/**
 * Producto entity (has identity). A good the store offers for sale.
 * Identified by its id; name and price are mutable state.
 */
final class Producto
{
    public function __construct(
        private readonly string $id,
        private string $name,
        private Dinero $price,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Dinero
    {
        return $this->price;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function changePrice(Dinero $price): void
    {
        $this->price = $price;
    }

    /** Identity equality: two products are equal if they share the same id. */
    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }
}
