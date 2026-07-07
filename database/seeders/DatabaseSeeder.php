<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Supermarket\Domain\Catalog\Product;
use Supermarket\Domain\Catalog\ProductRepository;
use Supermarket\Domain\Shared\Money;
use Supermarket\Domain\Stock\Shelf;
use Supermarket\Domain\Stock\ShelfRepository;
use Supermarket\Domain\Stock\Warehouse;
use Supermarket\Domain\Stock\WarehouseRepository;
use Supermarket\Infrastructure\Persistence\OfferModel;

/**
 * Seeds a small, meaningful demo dataset (idempotent) so the deployed API
 * and the CLI have something to operate on.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = app(ProductRepository::class);
        $shelves = app(ShelfRepository::class);
        $warehouses = app(WarehouseRepository::class);

        // [id, name, price(cents), shelfQty, warehouseQty, offerPercent]
        $demo = [
            ['p-1', 'Milk 1L', 150, 45, 500, 10],
            ['p-2', 'Bread', 300, 20, 160, 0],   // low shelf, warehouse near the 150 alert line
            ['p-3', 'Coffee 500g', 2500, 60, 800, 15],
        ];

        $now = now();

        foreach ($demo as [$id, $name, $price, $shelfQty, $warehouseQty, $offerPercent]) {
            $products->save(new Product($id, $name, new Money($price, 'ARS')));
            $shelves->save(new Shelf($id, $shelfQty));
            $warehouses->save(new Warehouse($id, $warehouseQty));

            if ($offerPercent > 0) {
                OfferModel::firstOrCreate(
                    ['product_id' => $id, 'percent' => $offerPercent],
                    ['valid_from' => $now->copy()->subDay(), 'valid_to' => $now->copy()->addDays(30)],
                );
            }
        }
    }
}
