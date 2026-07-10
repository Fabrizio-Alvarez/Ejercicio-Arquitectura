<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Stock\RegistrarReposicion;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;

uses(RefreshDatabase::class);

it('replenishes the shelf up to 50, decrements the warehouse, and persists both', function () {
    app(GondolaRepository::class)->save(new Gondola('p-1', 20));       // low (<30)
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));

    $outcome = app(RegistrarReposicion::class)->execute('p-1');

    expect($outcome->result->quantityToMove())->toBe(30)
        ->and($outcome->result->emitsAlert())->toBeFalse() // 500 - 30 = 470, not < 150
        ->and($outcome->alert)->toBeNull();

    // Persisted new levels.
    expect(app(GondolaRepository::class)->find('p-1')->quantity())->toBe(50) // 20 + 30
        ->and(app(DepositoRepository::class)->find('p-1')->quantity())->toBe(470);
});

it('emits a stock alert when the projected warehouse drops below 150', function () {
    app(GondolaRepository::class)->save(new Gondola('p-1', 20));
    app(DepositoRepository::class)->save(new Deposito('p-1', 160)); // 160 - 30 = 130 < 150

    $outcome = app(RegistrarReposicion::class)->execute('p-1');

    expect($outcome->alert)->not->toBeNull()
        ->and($outcome->alert->productId())->toBe('p-1')
        ->and($outcome->alert->cantidad())->toBe(130);
});

it('does nothing when the shelf is not low', function () {
    app(GondolaRepository::class)->save(new Gondola('p-1', 40)); // healthy
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));

    $outcome = app(RegistrarReposicion::class)->execute('p-1');

    expect($outcome->result->hasReplenishment())->toBeFalse()
        ->and(app(GondolaRepository::class)->find('p-1')->quantity())->toBe(40); // unchanged
});
