<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Catalogo\Oferta;
use Supermercado\Domain\Catalogo\OfertaRepository;

final class EloquentOfertaRepository implements OfertaRepository
{
    public function findByProduct(string $productId): array
    {
        return OfertaModel::where('product_id', $productId)
            ->get()
            ->map(fn (OfertaModel $row) => $this->toDomain($row))
            ->all();
    }

    public function all(): array
    {
        return OfertaModel::all()
            ->map(fn (OfertaModel $row) => $this->toDomain($row))
            ->all();
    }

    public function save(Oferta $oferta): void
    {
        OfertaModel::create([
            'product_id' => $oferta->productId(),
            'percent' => $oferta->percent(),
            'valid_from' => $oferta->validFrom(),
            'valid_to' => $oferta->validTo(),
        ]);
    }

    private function toDomain(OfertaModel $row): Oferta
    {
        /** @var \Illuminate\Support\Carbon $validFrom */
        $validFrom = $row->valid_from;
        /** @var \Illuminate\Support\Carbon $validTo */
        $validTo = $row->valid_to;

        return new Oferta(
            (string) $row->product_id,
            (float) $row->percent,
            $validFrom->toDateTimeImmutable(),
            $validTo->toDateTimeImmutable(),
        );
    }
}
