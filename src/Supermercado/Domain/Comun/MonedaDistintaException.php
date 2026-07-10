<?php

declare(strict_types=1);

namespace Supermercado\Domain\Comun;

final class MonedaDistintaException extends \DomainException
{
    public function __construct(string $left, string $right)
    {
        parent::__construct("Cannot combine different currencies: {$left} and {$right}.");
    }
}
