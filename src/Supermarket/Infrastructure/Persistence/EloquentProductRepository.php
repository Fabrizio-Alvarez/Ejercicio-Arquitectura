<?php

declare(strict_types=1);

namespace Supermarket\Infrastructure\Persistence;

use Supermarket\Domain\Catalog\Product;
use Supermarket\Domain\Catalog\ProductRepository;
use Supermarket\Domain\Shared\Money;

/**
 * Eloquent adapter implementing the domain ProductRepository port.
 * Translates between ProductModel rows and domain Product objects.
 */
final class EloquentProductRepository implements ProductRepository
{
    public function find(string $id): ?Product
    {
        $row = ProductModel::find($id);

        return $row !== null ? $this->toDomain($row) : null;
    }

    public function save(Product $product): void
    {
        ProductModel::updateOrCreate(
            ['id' => $product->id()],
            [
                'name' => $product->name(),
                'price_amount' => $product->price()->amount(),
                'price_currency' => $product->price()->currency(),
            ],
        );
    }

    public function all(): array
    {
        return ProductModel::all()
            ->map(fn (ProductModel $row) => $this->toDomain($row))
            ->all();
    }

    private function toDomain(ProductModel $row): Product
    {
        return new Product(
            (string) $row->id,
            (string) $row->name,
            new Money((int) $row->price_amount, (string) $row->price_currency),
        );
    }
}
