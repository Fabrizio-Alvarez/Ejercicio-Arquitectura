<?php

declare(strict_types=1);

namespace Supermarket\Application\Sales;

final class ItemRequest
{
    public function __construct(
        public readonly string $productId,
        public readonly int $quantity,
    ) {}
}
