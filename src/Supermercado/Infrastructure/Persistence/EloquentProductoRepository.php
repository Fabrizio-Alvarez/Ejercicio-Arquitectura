<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Dinero;

/**
 * Eloquent adapter implementing the domain ProductoRepository port.
 * Translates between ProductoModel rows and domain Producto objects.
 */
final class EloquentProductoRepository implements ProductoRepository
{
    public function find(string $id): ?Producto
    {
        $row = ProductoModel::find($id);

        return $row !== null ? $this->toDomain($row) : null;
    }

    public function save(Producto $product): void
    {
        ProductoModel::updateOrCreate(
            ['id' => $product->id()],
            [
                'name' => $product->name(),
                'price_amount' => $product->price()->amount(),
                'price_currency' => $product->price()->currency(),
            ],
        );
    }

    public function delete(string $id): void
    {
        ProductoModel::destroy($id);
    }

    public function all(): array
    {
        return ProductoModel::all()
            ->map(fn (ProductoModel $row) => $this->toDomain($row))
            ->all();
    }

    private function toDomain(ProductoModel $row): Producto
    {
        return new Producto(
            (string) $row->id,
            (string) $row->name,
            new Dinero((int) $row->price_amount, (string) $row->price_currency),
        );
    }
}
