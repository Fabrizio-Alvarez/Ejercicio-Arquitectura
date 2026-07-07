<?php

declare(strict_types=1);

namespace Supermarket\Application\Stock;

use Supermarket\Domain\Stock\ReplenishmentResult;
use Supermarket\Domain\Stock\StockAlert;

/**
 * Outcome of a replenishment action: the policy decision and, if the
 * warehouse dropped below threshold, the emitted stock alert.
 */
final class ReposicionOutcome
{
    public function __construct(
        public readonly ReplenishmentResult $result,
        public readonly ?StockAlert $alert,
    ) {}
}
