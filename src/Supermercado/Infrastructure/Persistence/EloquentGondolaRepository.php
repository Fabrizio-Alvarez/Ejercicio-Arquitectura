<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;

final class EloquentGondolaRepository implements GondolaRepository
{
    public function find(string $productId): ?Gondola
    {
        $row = GondolaModel::find($productId);

        return $row !== null ? $this->toDomain($row) : null;
    }

    public function save(Gondola $shelf): void
    {
        GondolaModel::updateOrCreate(
            ['product_id' => $shelf->productId()],
            ['quantity' => $shelf->quantity(), 'umbral_bajo' => $shelf->umbralBajo()],
        );
    }

    public function all(): array
    {
        return GondolaModel::all()
            ->map(fn (GondolaModel $row) => $this->toDomain($row))
            ->all();
    }

    private function toDomain(GondolaModel $row): Gondola
    {
        return new Gondola((string) $row->product_id, (int) $row->quantity, (int) ($row->umbral_bajo ?? Gondola::UMBRAL_BAJO));
    }
}
