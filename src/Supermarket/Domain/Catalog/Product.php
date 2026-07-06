<?php

declare(strict_types=1);

namespace Supermarket\Domain\Catalog;

use Supermarket\Domain\Shared\Money;

/**
 * Product entity (has identity). A good the store offers for sale.
 * Identified by its id; name and price are mutable state.
 */
final class Product
{
    public function __construct(
        private readonly string $id,
        private string $name,
        private Money $price,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function changePrice(Money $price): void
    {
        $this->price = $price;
    }

    /** Identity equality: two products are equal if they share the same id. */
    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }
}
