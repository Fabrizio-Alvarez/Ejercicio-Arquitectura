<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Ventas\CobrarProductos;
use Supermercado\Application\Ventas\CobrarRequest;
use Supermercado\Application\Ventas\ItemRequest;
use Supermercado\Application\Ventas\ProductoNoEncontradoException;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Ventas\VentaRepository;
use Supermercado\Domain\Ventas\EstadoDeVenta;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Infrastructure\Persistence\OfertaModel;

uses(RefreshDatabase::class);

it('prices items with the best active offer and registers a confirmed sale', function () {
    app(ProductoRepository::class)->save(new Producto('p-1', 'Milk', new Dinero(1000, 'ARS')));

    // 25% off, valid across a wide window so it is always active.
    OfertaModel::create([
        'product_id' => 'p-1',
        'percent' => 25.00,
        'valid_from' => '2020-01-01 00:00:00',
        'valid_to' => '2099-12-31 23:59:59',
    ]);

    $sale = app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 's-1',
        cashierId: 'cashier-1',
        customerName: 'Jane Doe',
        items: [new ItemRequest('p-1', 2)],
    ));

    // 2 x (1000 - 25%) = 2 x 750 = 1500
    expect($sale->status())->toBe(EstadoDeVenta::Confirmada)
        ->and($sale->lines())->toHaveCount(1)
        ->and($sale->total())->toEqual(new Dinero(1500, 'ARS'));

    // And it was persisted.
    expect(app(VentaRepository::class)->find('s-1'))->not->toBeNull();
});

it('charges full price when no offer is active', function () {
    app(ProductoRepository::class)->save(new Producto('p-1', 'Milk', new Dinero(1000, 'ARS')));

    $sale = app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 's-2',
        cashierId: 'cashier-1',
        customerName: 'John',
        items: [new ItemRequest('p-1', 1)],
    ));

    expect($sale->total())->toEqual(new Dinero(1000, 'ARS'));
});

it('fails when a requested product does not exist', function () {
    expect(fn () => app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 's-3',
        cashierId: 'cashier-1',
        customerName: 'Jane',
        items: [new ItemRequest('nope', 1)],
    )))->toThrow(ProductoNoEncontradoException::class);
});
