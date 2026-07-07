<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermarket\Domain\Stock\ShelfRepository;
use Supermarket\Domain\Stock\WarehouseRepository;

uses(RefreshDatabase::class);

describe('Shelf persistence', function () {
    it('persists and retrieves a shelf', function () {
        $repo = app(ShelfRepository::class);

        $repo->save(new \Supermarket\Domain\Stock\Shelf('p-1', 45));

        $found = $repo->find('p-1');

        expect($found)->not->toBeNull()
            ->and($found->quantity())->toBe(45)
            ->and($found->isLow())->toBeFalse();
    });

    it('saves the updated quantity after a restock', function () {
        $repo = app(ShelfRepository::class);

        $shelf = new \Supermarket\Domain\Stock\Shelf('p-1', 20);
        $repo->save($shelf);

        $shelf->restock(30);
        $repo->save($shelf);

        expect($repo->find('p-1')->quantity())->toBe(50);
    });
});

describe('Warehouse persistence', function () {
    it('persists and retrieves a warehouse', function () {
        $repo = app(WarehouseRepository::class);

        $repo->save(new \Supermarket\Domain\Stock\Warehouse('p-1', 200));

        $found = $repo->find('p-1');

        expect($found)->not->toBeNull()
            ->and($found->quantity())->toBe(200);
    });

    it('saves the updated quantity after a take', function () {
        $repo = app(WarehouseRepository::class);

        $warehouse = new \Supermarket\Domain\Stock\Warehouse('p-1', 200);
        $repo->save($warehouse);

        $warehouse->take(50);
        $repo->save($warehouse);

        expect($repo->find('p-1')->quantity())->toBe(150);
    });
});
