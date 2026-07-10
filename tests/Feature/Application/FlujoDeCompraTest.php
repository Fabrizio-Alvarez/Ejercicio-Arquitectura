<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
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
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;
use Supermercado\Domain\Ventas\MetodoDePago;

uses(RefreshDatabase::class);

it('descuenta la góndola y registra un movimiento de venta al cobrar', function () {
    app(ProductoRepository::class)->save(new Producto('p-1', 'Leche 1L', new Dinero(1000, 'ARS')));
    app(GondolaRepository::class)->save(new Gondola('p-1', 45));   // góndola sana
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));

    app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 'v-1',
        cashierId: 'c-1',
        customerName: 'Jane Doe',
        items: [new ItemRequest('p-1', 2)],
        metodoDePago: MetodoDePago::Efectivo,
    ));

    // La góndola bajó 2; el depósito no se toca con la venta.
    expect(app(GondolaRepository::class)->find('p-1')->quantity())->toBe(43)
        ->and(app(DepositoRepository::class)->find('p-1')->quantity())->toBe(500);

    // El depósito registró el movimiento de venta referenciando la venta.
    $movs = app(MovimientoDeStockRepository::class)->findByProducto('p-1');
    expect($movs)->toHaveCount(1)
        ->and($movs[0]->tipo())->toBe(TipoDeMovimiento::Venta)
        ->and($movs[0]->cantidad())->toBe(2)
        ->and($movs[0]->ubicacion())->toBe(UbicacionDeStock::Gondola)
        ->and($movs[0]->referencia())->toBe('v-1');
});

it('reacciona a la alerta de góndola baja reponiendo automáticamente desde el depósito', function () {
    app(ProductoRepository::class)->save(new Producto('p-1', 'Leche 1L', new Dinero(1000, 'ARS')));
    app(GondolaRepository::class)->save(new Gondola('p-1', 25));   // ya bajo (< 30)
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));

    app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 'v-2',
        cashierId: 'c-1',
        customerName: 'Jane Doe',
        items: [new ItemRequest('p-1', 1)],   // 25 -> 24 (sigue bajo) -> alerta -> repositor repone
        metodoDePago: MetodoDePago::TarjetaCredito,
    ));

    // Tras la venta (24) y la reposición automática (a 50): góndola 50, depósito 500 - 26 = 474.
    expect(app(GondolaRepository::class)->find('p-1')->quantity())->toBe(50)
        ->and(app(DepositoRepository::class)->find('p-1')->quantity())->toBe(474);

    // Quedan dos movimientos: la venta y la reposición.
    $movs = app(MovimientoDeStockRepository::class)->findByProducto('p-1');
    $tipos = array_map(fn ($m) => $m->tipo()->value, $movs);
    expect($movs)->toHaveCount(2)
        ->and($tipos)->toContain('venta', 'reposicion');
});

it('no descuenta productos cuya góndola no se trackea (solo registra el movimiento)', function () {
    app(ProductoRepository::class)->save(new Producto('p-1', 'Leche 1L', new Dinero(1000, 'ARS')));
    // Sin góndola ni depósito para p-1.

    app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 'v-3',
        cashierId: 'c-1',
        customerName: 'Jane Doe',
        items: [new ItemRequest('p-1', 1)],
        metodoDePago: MetodoDePago::QR,
    ));

    // No hay góndola, pero el depósito igual registra el movimiento.
    expect(app(GondolaRepository::class)->find('p-1'))->toBeNull();
    expect(app(MovimientoDeStockRepository::class)->findByProducto('p-1'))->toHaveCount(1);
});
