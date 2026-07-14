<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\LineaDeVenta;
use Supermercado\Domain\Ventas\VentaRepository;
use Supermercado\Domain\Ventas\EstadoDeVenta;
use Supermercado\Domain\Comun\Dinero;

uses(RefreshDatabase::class);

it('persists and retrieves a confirmed sale with its lines and total', function () {
    $repo = app(VentaRepository::class);

    $sale = new Venta('s-1', 'cashier-1', 'Jane Doe', new \DateTimeImmutable('2026-01-15 10:00:00'));
    $sale->addLine(new LineaDeVenta('p-1', 'Milk', 2, new Dinero(150, 'ARS')));
    $sale->addLine(new LineaDeVenta('p-2', 'Bread', 1, new Dinero(300, 'ARS')));
    $sale->marcarEsperandoPago();
    $sale->confirm();

    $repo->save($sale);

    $found = $repo->find('s-1');

    expect($found)->not->toBeNull()
        ->and($found->status())->toBe(EstadoDeVenta::Confirmada)
        ->and($found->customerName())->toBe('Jane Doe')
        ->and($found->cashierId())->toBe('cashier-1')
        ->and($found->lines())->toHaveCount(2)
        ->and($found->total())->toEqual(new Dinero(600, 'ARS'));
});

it('returns null for a missing sale', function () {
    expect(app(VentaRepository::class)->find('nope'))->toBeNull();
});

it('persists a pending sale and keeps its status', function () {
    $repo = app(VentaRepository::class);

    $sale = new Venta('s-2', 'cashier-1', 'John', new \DateTimeImmutable('2026-01-15'));
    $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));

    $repo->save($sale);

    $found = $repo->find('s-2');

    expect($found->status())->toBe(EstadoDeVenta::Pendiente)
        ->and($found->lines())->toHaveCount(1);
});

it('lists all persisted sales', function () {
    $repo = app(VentaRepository::class);

    foreach (['s-1', 's-2'] as $id) {
        $sale = new Venta($id, 'cashier-1', 'Jane', new \DateTimeImmutable('2026-01-15'));
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
        $sale->marcarEsperandoPago();
        $sale->confirm();
        $repo->save($sale);
    }

    expect($repo->all())->toHaveCount(2);
});
