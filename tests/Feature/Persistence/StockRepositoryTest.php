<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\DepositoRepository;

uses(RefreshDatabase::class);

describe('Gondola persistence', function () {
    it('persists and retrieves a shelf', function () {
        $repo = app(GondolaRepository::class);

        $repo->save(new \Supermercado\Domain\Stock\Gondola('p-1', 45));

        $found = $repo->find('p-1');

        expect($found)->not->toBeNull()
            ->and($found->quantity())->toBe(45)
            ->and($found->isLow())->toBeFalse();
    });

    it('saves the updated quantity after a restock', function () {
        $repo = app(GondolaRepository::class);

        $shelf = new \Supermercado\Domain\Stock\Gondola('p-1', 20);
        $repo->save($shelf);

        $shelf->restock(30);
        $repo->save($shelf);

        expect($repo->find('p-1')->quantity())->toBe(50);
    });
});

describe('Deposito persistence', function () {
    it('persists and retrieves a warehouse', function () {
        $repo = app(DepositoRepository::class);

        $repo->save(new \Supermercado\Domain\Stock\Deposito('p-1', 200));

        $found = $repo->find('p-1');

        expect($found)->not->toBeNull()
            ->and($found->quantity())->toBe(200);
    });

    it('saves the updated quantity after a take', function () {
        $repo = app(DepositoRepository::class);

        $warehouse = new \Supermercado\Domain\Stock\Deposito('p-1', 200);
        $repo->save($warehouse);

        $warehouse->take(50);
        $repo->save($warehouse);

        expect($repo->find('p-1')->quantity())->toBe(150);
    });
});
