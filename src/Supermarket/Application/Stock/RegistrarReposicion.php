<?php

declare(strict_types=1);

namespace Supermarket\Application\Stock;

use Supermarket\Domain\Stock\ReplenishmentPolicy;
use Supermarket\Domain\Stock\ShelfRepository;
use Supermarket\Domain\Stock\StockAlert;
use Supermarket\Domain\Stock\WarehouseRepository;

/**
 * Use case: RegistrarReposicion (+ EmitirAlerta).
 *
 * A stock clerk replenishes a product's shelf from the warehouse. The
 * replenishment policy decides how much to move (and whether to emit a
 * low-stock alert). The use case applies the move, persists the new shelf
 * and warehouse levels, and returns the outcome (including the alert, if any).
 */
final class RegistrarReposicion
{
    public function __construct(
        private readonly ShelfRepository $shelves,
        private readonly WarehouseRepository $warehouses,
        private readonly ReplenishmentPolicy $policy,
    ) {}

    public function execute(string $productId): ReposicionOutcome
    {
        $shelf = $this->shelves->find($productId);
        $warehouse = $this->warehouses->find($productId);

        if ($shelf === null || $warehouse === null) {
            throw new \DomainException("No stock is being tracked for product {$productId}.");
        }

        $result = $this->policy->decide($shelf, $warehouse);

        if ($result->hasReplenishment()) {
            $shelf->restock($result->quantityToMove());
            $warehouse->take($result->quantityToMove());

            $this->shelves->save($shelf);
            $this->warehouses->save($warehouse);
        }

        $alert = $result->emitsAlert()
            ? new StockAlert($productId, $warehouse->quantity(), new \DateTimeImmutable('now'))
            : null;

        return new ReposicionOutcome($result, $alert);
    }
}
