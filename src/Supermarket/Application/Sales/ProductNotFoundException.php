<?php

declare(strict_types=1);

namespace Supermarket\Application\Sales;

final class ProductNotFoundException extends \DomainException
{
    public static function forId(string $productId): self
    {
        return new self("Product not found: {$productId}.");
    }
}
