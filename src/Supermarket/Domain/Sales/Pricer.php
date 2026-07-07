<?php

declare(strict_types=1);

namespace Supermarket\Domain\Sales;

use Supermarket\Domain\Catalog\Offer;
use Supermarket\Domain\Catalog\Product;

/**
 * Domain service: prices a product for a sale, applying the BEST active
 * offer that covers it (or no discount if none is active).
 */
final class Pricer
{
    /**
     * @param Offer[] $offers
     */
    public function price(Product $product, int $quantity, array $offers, \DateTimeImmutable $at): SaleLine
    {
        $best = $this->bestActiveOfferFor($product->id(), $offers, $at);

        $unitPrice = $best !== null
            ? $best->applyTo($product->price())
            : $product->price();

        return new SaleLine($product->id(), $product->name(), $quantity, $unitPrice);
    }

    /**
     * @param Offer[] $offers
     */
    private function bestActiveOfferFor(string $productId, array $offers, \DateTimeImmutable $at): ?Offer
    {
        $best = null;

        foreach ($offers as $offer) {
            if (! $offer instanceof Offer) {
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
