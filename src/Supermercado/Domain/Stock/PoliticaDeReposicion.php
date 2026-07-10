<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

/**
 * Domain service implementing the supermarket's replenishment rule:
 *
 *   "When a shelf has fewer than 30 units, the stock clerk replenishes it
 *    up to 50, taking units from the warehouse. If, after replenishing,
 *    the warehouse falls below 150, a low-stock alert must be emitted."
 *
 * decide() is PURE: it inspects the current shelf/warehouse state and
 * returns what WOULD happen, without mutating anything. The application
 * layer applies the result and emits the alert if requested.
 */
final class PoliticaDeReposicion
{
    public const LOW_SHELF_THRESHOLD = 30;
    public const TARGET_LEVEL = 50;
    public const LOW_WAREHOUSE_THRESHOLD = 150;

    public function decide(Gondola $shelf, Deposito $warehouse): DecisionDeReposicion
    {
        if ($shelf->quantity() >= self::LOW_SHELF_THRESHOLD) {
            // Gondola is healthy: nothing to move, no alert.
            return new DecisionDeReposicion($shelf->productId(), 0, false);
        }

        $needed = self::TARGET_LEVEL - $shelf->quantity(); // always > 0 since shelf < 30 < 50
        $toMove = min($needed, $warehouse->quantity()); // never move more than the warehouse holds
        $projectedWarehouse = $warehouse->quantity() - $toMove;
        $emitsAlert = $projectedWarehouse < self::LOW_WAREHOUSE_THRESHOLD;

        return new DecisionDeReposicion($shelf->productId(), $toMove, $emitsAlert);
    }
}
