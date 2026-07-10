<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

use Supermercado\Domain\Stock\DecisionDeReposicion;
use Supermercado\Domain\Stock\AlertaDeStock;

/**
 * Outcome of a replenishment action: the policy decision and, if the
 * warehouse dropped below threshold, the emitted stock alert.
 */
final class ResultadoDeReposicion
{
    public function __construct(
        public readonly DecisionDeReposicion $result,
        public readonly ?AlertaDeStock $alert,
    ) {}
}
