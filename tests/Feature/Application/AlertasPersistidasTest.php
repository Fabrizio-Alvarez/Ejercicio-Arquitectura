<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Stock\RegistrarReposicion;
use Supermercado\Application\Ventas\CobrarProductos;
use Supermercado\Application\Ventas\CobrarRequest;
use Supermercado\Application\Ventas\ItemRequest;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Stock\AlertaDeStockRepository;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\UbicacionDeStock;
use Supermercado\Domain\Ventas\MetodoDePago;

uses(RefreshDatabase::class);

it('persiste una alerta de góndola cuando una venta deja la góndola bajo 30', function () {
    app(ProductoRepository::class)->save(new Producto('p-1', 'Leche 1L', new Dinero(1000, 'ARS')));
    app(GondolaRepository::class)->save(new Gondola('p-1', 25));   // ya bajo (< 30)
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));

    app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 'v-1',
        cashierId: 'c-1',
        customerName: 'Jane Doe',
        items: [new ItemRequest('p-1', 1)],   // 25 -> 24, sigue bajo -> alerta
        metodoDePago: MetodoDePago::Efectivo,
    ));

    $alertas = app(AlertaDeStockRepository::class)->findByProducto('p-1');

    // Al menos una alerta de góndola persistida.
    expect($alertas)->not->toBeEmpty();
    expect(array_filter($alertas, fn ($a) => $a->ubicacion() === UbicacionDeStock::Gondola))->not->toBeEmpty();
});

it('persiste una alerta de depósito cuando una reposición deja el depósito bajo 150', function () {
    app(GondolaRepository::class)->save(new Gondola('p-1', 20));   // bajo (< 30)
    app(DepositoRepository::class)->save(new Deposito('p-1', 160)); // 160 - 30 = 130 < 150

    app(RegistrarReposicion::class)->execute('p-1');

    $alertas = app(AlertaDeStockRepository::class)->findByProducto('p-1');

    expect($alertas)->toHaveCount(1)
        ->and($alertas[0]->ubicacion())->toBe(UbicacionDeStock::Deposito)
        ->and($alertas[0]->cantidad())->toBe(130);
});

it('no persiste alerta cuando la reposición no deja el depósito bajo', function () {
    app(GondolaRepository::class)->save(new Gondola('p-1', 20));
    app(DepositoRepository::class)->save(new Deposito('p-1', 500)); // 500 - 30 = 470, no baja de 150

    app(RegistrarReposicion::class)->execute('p-1');

    expect(app(AlertaDeStockRepository::class)->all())->toBeEmpty();
});
