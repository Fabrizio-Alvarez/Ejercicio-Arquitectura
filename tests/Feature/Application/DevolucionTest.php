<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Ventas\CobrarProductos;
use Supermercado\Application\Ventas\CobrarRequest;
use Supermercado\Application\Ventas\ItemRequest;
use Supermercado\Application\Ventas\ProcesarDevolucion;
use Supermercado\Application\Ventas\VentaNoEncontradaException;
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
use Supermercado\Domain\Ventas\ItemDevolucion;
use Supermercado\Domain\Ventas\MetodoDePago;

uses(RefreshDatabase::class);

it('restaura stock en la góndola y registra movimiento al procesar una devolución', function () {
    app(ProductoRepository::class)->save(new Producto('p-1', 'Leche 1L', new Dinero(1000, 'ARS')));
    app(GondolaRepository::class)->save(new Gondola('p-1', 45));
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));

    // Vender 5 unidades: góndola 45 → 40.
    app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 'v-1',
        cashierId: 'c-1',
        customerName: 'Jane Doe',
        items: [new ItemRequest('p-1', 5)],
        metodoDePago: MetodoDePago::Efectivo,
    ));

    expect(app(GondolaRepository::class)->find('p-1')->quantity())->toBe(40);

    // Devolver 2 unidades.
    app(ProcesarDevolucion::class)->execute('v-1', [
        new ItemDevolucion('p-1', 2),
    ]);

    // Stock restaurado: 40 + 2 = 42.
    expect(app(GondolaRepository::class)->find('p-1')->quantity())->toBe(42)
        ->and(app(DepositoRepository::class)->find('p-1')->quantity())->toBe(500);

    // Dos movimientos: la venta y la devolución.
    $movs = app(MovimientoDeStockRepository::class)->findByProducto('p-1');
    expect($movs)->toHaveCount(2);
    $devolucion = array_values(array_filter($movs, fn ($m) => $m->tipo() === TipoDeMovimiento::Devolucion));
    expect($devolucion)->toHaveCount(1)
        ->and($devolucion[0]->cantidad())->toBe(2)
        ->and($devolucion[0]->ubicacion())->toBe(UbicacionDeStock::Gondola)
        ->and($devolucion[0]->referencia())->toBe('v-1');
});

it('falla si la venta no existe', function () {
    expect(fn () => app(ProcesarDevolucion::class)->execute('nope', [
        new ItemDevolucion('p-1', 1),
    ]))->toThrow(VentaNoEncontradaException::class);
});

it('falla si se intenta devolver más de lo vendido', function () {
    app(ProductoRepository::class)->save(new Producto('p-1', 'Leche 1L', new Dinero(1000, 'ARS')));
    app(GondolaRepository::class)->save(new Gondola('p-1', 45));

    app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 'v-2',
        cashierId: 'c-1',
        customerName: 'Jane Doe',
        items: [new ItemRequest('p-1', 3)],
    ));

    expect(fn () => app(ProcesarDevolucion::class)->execute('v-2', [
        new ItemDevolucion('p-1', 10),
    ]))->toThrow(\DomainException::class);
});
