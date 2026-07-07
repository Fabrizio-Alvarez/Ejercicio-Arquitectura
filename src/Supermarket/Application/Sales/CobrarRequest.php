<?php

declare(strict_types=1);

namespace Supermarket\Application\Sales;

/**
 * Request DTO for the CobrarProductos (checkout) use case.
 */
final class CobrarRequest
{
    /**
     * @param ItemRequest[] $items
     */
    public function __construct(
        public readonly string $saleId,
        public readonly string $cashierId,
        public readonly string $customerName,
        public readonly array $items,
    ) {}
}
