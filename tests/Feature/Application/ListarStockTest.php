<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermarket\Application\Stock\ListarStock;
use Supermarket\Domain\Stock\Shelf;
use Supermarket\Domain\Stock\ShelfRepository;
use Supermarket\Domain\Stock\Warehouse;
use Supermarket\Domain\Stock\WarehouseRepository;

uses(RefreshDatabase::class);

it('lists the stock view across all products with low flags', function () {
    app(ShelfRepository::class)->save(new Shelf('p-1', 45));       // healthy
    app(ShelfRepository::class)->save(new Shelf('p-2', 20));       // low shelf
    app(WarehouseRepository::class)->save(new Warehouse('p-1', 500));
    app(WarehouseRepository::class)->save(new Warehouse('p-2', 120)); // low warehouse

    $views = app(ListarStock::class)->execute();

    $byProduct = collect($views)->keyBy('productId');

    expect($views)->toHaveCount(2)
        ->and($byProduct['p-1']->shelfQuantity)->toBe(45)
        ->and($byProduct['p-1']->shelfLow)->toBeFalse()
        ->and($byProduct['p-1']->warehouseQuantity)->toBe(500)
        ->and($byProduct['p-2']->shelfLow)->toBeTrue()
        ->and($byProduct['p-2']->warehouseLow)->toBeTrue();
});
