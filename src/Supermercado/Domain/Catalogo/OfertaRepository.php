<?php

declare(strict_types=1);

namespace Supermercado\Domain\Catalogo;

/**
 * Repository port for offers. The application reads offers to price sales
 * and manages them (create) via the catalog UI.
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
    public function save(Oferta $oferta): void;
}
