<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermarket\Application\Stock\RegistrarReposicion;
use Supermarket\Domain\Stock\Shelf;
use Supermarket\Domain\Stock\ShelfRepository;
use Supermarket\Domain\Stock\Warehouse;
use Supermarket\Domain\Stock\WarehouseRepository;

uses(RefreshDatabase::class);

it('replenishes the shelf up to 50, decrements the warehouse, and persists both', function () {
    app(ShelfRepository::class)->save(new Shelf('p-1', 20));       // low (<30)
    app(WarehouseRepository::class)->save(new Warehouse('p-1', 500));

    $outcome = app(RegistrarReposicion::class)->execute('p-1');

    expect($outcome->result->quantityToMove())->toBe(30)
        ->and($outcome->result->emitsAlert())->toBeFalse() // 500 - 30 = 470, not < 150
        ->and($outcome->alert)->toBeNull();

    // Persisted new levels.
    expect(app(ShelfRepository::class)->find('p-1')->quantity())->toBe(50) // 20 + 30
        ->and(app(WarehouseRepository::class)->find('p-1')->quantity())->toBe(470);
});

it('emits a stock alert when the projected warehouse drops below 150', function () {
    app(ShelfRepository::class)->save(new Shelf('p-1', 20));
    app(WarehouseRepository::class)->save(new Warehouse('p-1', 160)); // 160 - 30 = 130 < 150

    $outcome = app(RegistrarReposicion::class)->execute('p-1');

    expect($outcome->alert)->not->toBeNull()
        ->and($outcome->alert->productId())->toBe('p-1')
        ->and($outcome->alert->warehouseQuantity())->toBe(130);
});

it('does nothing when the shelf is not low', function () {
    app(ShelfRepository::class)->save(new Shelf('p-1', 40)); // healthy
    app(WarehouseRepository::class)->save(new Warehouse('p-1', 500));

    $outcome = app(RegistrarReposicion::class)->execute('p-1');

    expect($outcome->result->hasReplenishment())->toBeFalse()
        ->and(app(ShelfRepository::class)->find('p-1')->quantity())->toBe(40); // unchanged
});
