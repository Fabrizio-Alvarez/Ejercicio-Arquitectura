<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Stock\AlertaDeStockRepository;
use Supermercado\Domain\Stock\UbicacionDeStock;

uses(RefreshDatabase::class);

describe('Alerta persistence', function () {
    it('persists and retrieves a stock alert', function () {
        $repo = app(AlertaDeStockRepository::class);

        $repo->save(new AlertaDeStock('p-1', UbicacionDeStock::Gondola, 25, new \DateTimeImmutable('2026-01-01 10:00:00')));

        $all = $repo->all();

        expect($all)->toHaveCount(1)
            ->and($all[0]->productId())->toBe('p-1')
            ->and($all[0]->ubicacion())->toBe(UbicacionDeStock::Gondola)
            ->and($all[0]->cantidad())->toBe(25);
    });

    it('finds alerts by product', function () {
        $repo = app(AlertaDeStockRepository::class);

        $repo->save(new AlertaDeStock('p-1', UbicacionDeStock::Gondola, 25, new \DateTimeImmutable('2026-01-01 10:00:00')));
        $repo->save(new AlertaDeStock('p-2', UbicacionDeStock::Deposito, 130, new \DateTimeImmutable('2026-01-01 11:00:00')));
        $repo->save(new AlertaDeStock('p-1', UbicacionDeStock::Deposito, 140, new \DateTimeImmutable('2026-01-01 12:00:00')));

        expect($repo->findByProducto('p-1'))->toHaveCount(2)
            ->and($repo->findByProducto('p-2'))->toHaveCount(1)
            ->and($repo->findByProducto('p-3'))->toHaveCount(0);
    });

    it('returns the most recent alert first', function () {
        $repo = app(AlertaDeStockRepository::class);

        $repo->save(new AlertaDeStock('p-1', UbicacionDeStock::Gondola, 25, new \DateTimeImmutable('2026-01-01 10:00:00')));
        $repo->save(new AlertaDeStock('p-1', UbicacionDeStock::Deposito, 140, new \DateTimeImmutable('2026-01-02 10:00:00')));

        $all = $repo->all();

        expect($all[0]->cantidad())->toBe(140)   // la del 2026-01-02 primero
            ->and($all[1]->cantidad())->toBe(25);
    });
});
