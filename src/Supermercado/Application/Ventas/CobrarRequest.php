<?php

declare(strict_types=1);

namespace Supermercado\Application\Ventas;

use Supermercado\Domain\Ventas\MetodoDePago;

/**
 * Request DTO para el caso de uso CobrarProductos (checkout).
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
        public readonly MetodoDePago $metodoDePago = MetodoDePago::Efectivo,
    ) {}
}
