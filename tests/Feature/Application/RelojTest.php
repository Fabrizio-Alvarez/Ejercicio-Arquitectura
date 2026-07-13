<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Stock\RegistrarReabastecimiento;
use Supermercado\Application\Ventas\CobrarProductos;
use Supermercado\Application\Ventas\CobrarRequest;
use Supermercado\Application\Ventas\ItemRequest;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Ventas\MetodoDePago;

uses(RefreshDatabase::class);

it('estampa la hora del FixedClock en el movimiento de venta (sin depender del reloj real)', function () {
    fijarReloj('2026-01-15T10:00:00+00:00');

    app(ProductoRepository::class)->save(new Producto('p-1', 'Leche 1L', new Dinero(1000, 'ARS')));
    app(GondolaRepository::class)->save(new Gondola('p-1', 40));

    app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 'v-1',
        cashierId: 'c-1',
        customerName: 'Jane Doe',
        items: [new ItemRequest('p-1', 1)],
        metodoDePago: MetodoDePago::Efectivo,
    ));

    $esperado = (new \DateTimeImmutable('2026-01-15T10:00:00+00:00'))->getTimestamp();
    $movimientos = app(MovimientoDeStockRepository::class)->findByProducto('p-1');

    expect($movimientos)->not->toBeEmpty();
    foreach ($movimientos as $m) {
        expect($m->fecha()->getTimestamp())->toBe($esperado);
    }
});

it('estampa la hora del FixedClock en el movimiento de reabastecimiento', function () {
    fijarReloj('2026-06-01T08:30:00+00:00');

    app(DepositoRepository::class)->save(new Deposito('p-1', 130));

    app(RegistrarReabastecimiento::class)->execute('p-1', 200, 'Distribuidora SA');

    $esperado = (new \DateTimeImmutable('2026-06-01T08:30:00+00:00'))->getTimestamp();
    $movimiento = app(MovimientoDeStockRepository::class)->findByProducto('p-1')[0];

    expect($movimiento->fecha()->getTimestamp())->toBe($esperado);
});
