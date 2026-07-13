<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Reportes\ObtenerReporteMovimientos;
use Supermercado\Application\Reportes\ObtenerReporteVentas;
use Supermercado\Application\Stock\RegistrarAjuste;
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
use Supermercado\Domain\Stock\UbicacionDeStock;
use Supermercado\Domain\Ventas\MetodoDePago;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(ProductoRepository::class)->save(new Producto('p-1', 'Leche 1L', new Dinero(1000, 'ARS')));
    app(GondolaRepository::class)->save(new Gondola('p-1', 100));
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));
    fijarReloj('2026-01-15T10:00:00+00:00');
});

function vender(string $saleId, int $qty, string $day): void
{
    fijarReloj($day);
    app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: $saleId,
        cashierId: 'c-1',
        customerName: 'Cliente',
        items: [new ItemRequest('p-1', $qty)],
        metodoDePago: MetodoDePago::Efectivo,
    ));
}

it('agrega KPIs de ventas confirmadas', function () {
    vender('v-1', 2, '2026-01-15T10:00:00+00:00');
    vender('v-2', 1, '2026-01-16T10:00:00+00:00');

    $view = app(ObtenerReporteVentas::class)->execute();

    expect($view->cantidadVentas)->toBe(2)
        ->and($view->totalGeneral)->toBe(30.0)
        ->and($view->ticketPromedio)->toBe(15.0);
});

it('agrupa ventas por día con total y cantidad', function () {
    vender('v-1', 2, '2026-01-15T10:00:00+00:00');
    vender('v-2', 1, '2026-01-16T10:00:00+00:00');

    $view = app(ObtenerReporteVentas::class)->execute();

    expect($view->ventasPorDia)->toHaveCount(2)
        ->and($view->ventasPorDia[0]['fecha'])->toBe('2026-01-15')
        ->and($view->ventasPorDia[0]['total'])->toBe(20.0)
        ->and($view->ventasPorDia[1]['fecha'])->toBe('2026-01-16');
});

it('genera top productos por unidades', function () {
    vender('v-1', 2, '2026-01-15T10:00:00+00:00');
    vender('v-2', 1, '2026-01-15T11:00:00+00:00');

    $view = app(ObtenerReporteVentas::class)->execute();

    expect($view->topProductos[0]['productoId'])->toBe('p-1')
        ->and($view->topProductos[0]['unidades'])->toBe(3)
        ->and($view->topProductos[0]['total'])->toBe(30.0);
});

it('maneja el caso sin ventas (vacío)', function () {
    $view = app(ObtenerReporteVentas::class)->execute();

    expect($view->cantidadVentas)->toBe(0)
        ->and($view->totalGeneral)->toBe(0.0)
        ->and($view->ventasPorDia)->toBeEmpty()
        ->and($view->topProductos)->toBeEmpty();
});

it('agrupa movimientos por tipo', function () {
    vender('v-1', 2, '2026-01-15T10:00:00+00:00');
    app(RegistrarAjuste::class)->execute('p-1', UbicacionDeStock::Deposito, 10, 'Conteo');

    $view = app(ObtenerReporteMovimientos::class)->execute();

    $tipos = array_column($view->movimientosPorTipo, 'tipo');
    expect($tipos)->toContain('venta', 'ajuste');

    $venta = array_values(array_filter($view->movimientosPorTipo, fn ($m) => $m['tipo'] === 'venta'))[0];
    expect($venta['cantidad'])->toBe(1)
        ->and($venta['unidades'])->toBe(2);
});

it('renderiza la página de reportes (cajero)', function () {
    $this->actingAs(cajero())->get('/reportes')->assertOk();
});

it('el repositor no puede ver reportes', function () {
    $this->actingAs(repositor())->get('/reportes')->assertRedirect('/tablero');
});
