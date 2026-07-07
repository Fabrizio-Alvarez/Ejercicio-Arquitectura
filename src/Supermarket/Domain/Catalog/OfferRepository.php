<?php

declare(strict_types=1);

namespace Supermarket\Domain\Catalog;

/**
 * Read-only repository port for offers. Offers are loaded by an external
 * system directly into the data source (per the spec); the application
 * only reads them to price sales.
 */
interface OfferRepository
{
    /**
     * @return Offer[]
     */
    public function findByProduct(string $productId): array;

    /**
     * @return Offer[]
     */
    public function all(): array;
}
