<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

use Supermercado\Domain\Catalogo\Oferta;
use Supermercado\Domain\Catalogo\Producto;

/**
 * Domain service: prices a product for a sale, applying the BEST active
 * offer that covers it (or no discount if none is active).
 */
final class Cotizador
{
    /**
     * @param Oferta[] $offers
     */
    public function price(Producto $product, int $quantity, array $offers, \DateTimeImmutable $at): LineaDeVenta
    {
        $best = $this->bestActiveOfferFor($product->id(), $offers, $at);

        $unitPrice = $best !== null
            ? $best->applyTo($product->price())
            : $product->price();

        return new LineaDeVenta($product->id(), $product->name(), $quantity, $unitPrice);
    }

    /**
     * @param Oferta[] $offers
     */
    private function bestActiveOfferFor(string $productId, array $offers, \DateTimeImmutable $at): ?Oferta
    {
        $best = null;

        foreach ($offers as $offer) {
            if (! $offer instanceof Oferta) {
                continue;
            }

            if ($offer->covers($productId) && $offer->isActive($at)) {
                if ($best === null || $offer->percent() > $best->percent()) {
                    $best = $offer;
                }
            }
        }

        return $best;
    }
}
