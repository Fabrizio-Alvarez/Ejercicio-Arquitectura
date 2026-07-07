<?php

declare(strict_types=1);

namespace Supermarket\Infrastructure\Persistence;

use Supermarket\Domain\Catalog\Offer;
use Supermarket\Domain\Catalog\OfferRepository;

final class EloquentOfferRepository implements OfferRepository
{
    public function findByProduct(string $productId): array
    {
        return OfferModel::where('product_id', $productId)
            ->get()
            ->map(fn (OfferModel $row) => $this->toDomain($row))
            ->all();
    }

    public function all(): array
    {
        return OfferModel::all()
            ->map(fn (OfferModel $row) => $this->toDomain($row))
            ->all();
    }

    private function toDomain(OfferModel $row): Offer
    {
        /** @var \Illuminate\Support\Carbon $validFrom */
        $validFrom = $row->valid_from;
        /** @var \Illuminate\Support\Carbon $validTo */
        $validTo = $row->valid_to;

        return new Offer(
            (string) $row->product_id,
            (float) $row->percent,
            $validFrom->toDateTimeImmutable(),
            $validTo->toDateTimeImmutable(),
        );
    }
}
