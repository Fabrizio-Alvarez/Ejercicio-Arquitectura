<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Stock\ListarStock;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;

uses(RefreshDatabase::class);

it('lists the stock view across all products with low flags', function () {
    app(GondolaRepository::class)->save(new Gondola('p-1', 45));       // healthy
    app(GondolaRepository::class)->save(new Gondola('p-2', 20));       // low shelf
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));
    app(DepositoRepository::class)->save(new Deposito('p-2', 120)); // low warehouse

    $views = app(ListarStock::class)->execute();

    $byProduct = collect($views)->keyBy('productId');

    expect($views)->toHaveCount(2)
        ->and($byProduct['p-1']->shelfQuantity)->toBe(45)
        ->and($byProduct['p-1']->shelfLow)->toBeFalse()
        ->and($byProduct['p-1']->warehouseQuantity)->toBe(500)
        ->and($byProduct['p-2']->shelfLow)->toBeTrue()
        ->and($byProduct['p-2']->warehouseLow)->toBeTrue();
});
