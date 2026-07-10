<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Infrastructure\Persistence\OfertaModel;

/**
 * Seeds a small, meaningful demo dataset (idempotent) so the deployed API
 * and the CLI have something to operate on.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = app(ProductoRepository::class);
        $shelves = app(GondolaRepository::class);
        $warehouses = app(DepositoRepository::class);

        // [id, nombre, precio(cents), gondola, deposito, % oferta]
        $demo = [
            ['p-1', 'Leche 1L', 150, 45, 500, 10],
            ['p-2', 'Pan', 300, 20, 160, 0],   // góndola baja, depósito cerca del umbral de alerta (150)
            ['p-3', 'Café 500g', 2500, 60, 800, 15],
        ];

        $now = now();

        foreach ($demo as [$id, $name, $price, $shelfQty, $warehouseQty, $offerPercent]) {
            $products->save(new Producto($id, $name, new Dinero($price, 'ARS')));
            $shelves->save(new Gondola($id, $shelfQty));
            $warehouses->save(new Deposito($id, $warehouseQty));

            if ($offerPercent > 0) {
                OfertaModel::firstOrCreate(
                    ['product_id' => $id, 'percent' => $offerPercent],
                    ['valid_from' => $now->copy()->subDay(), 'valid_to' => $now->copy()->addDays(30)],
                );
            }
        }
    }
}
