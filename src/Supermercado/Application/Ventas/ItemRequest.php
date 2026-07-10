<?php

declare(strict_types=1);

namespace Supermercado\Application\Ventas;

final class ItemRequest
{
    public function __construct(
        public readonly string $productId,
        public readonly int $quantity,
    ) {}
}
