<?php

declare(strict_types=1);

namespace Supermercado\Domain\Catalogo;

/**
 * Read-only repository port for offers. Offers are loaded by an external
 * system directly into the data source (per the spec); the application
 * only reads them to price sales.
 */
interface OfertaRepository
{
    /**
     * @return Oferta[]
     */
    public function findByProduct(string $productId): array;

    /**
     * @return Oferta[]
     */
    public function all(): array;
}
