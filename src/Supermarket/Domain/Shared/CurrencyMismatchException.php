<?php

declare(strict_types=1);

namespace Supermarket\Domain\Shared;

final class CurrencyMismatchException extends \DomainException
{
    public function __construct(string $left, string $right)
    {
        parent::__construct("Cannot combine different currencies: {$left} and {$right}.");
    }
}
