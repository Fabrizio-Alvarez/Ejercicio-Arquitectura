<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Tableros\ObtenerTableroCajero;
use Supermercado\Application\Tableros\ObtenerTableroDepositista;
use Supermercado\Application\Tableros\ObtenerTableroRepositor;
use Supermercado\Application\Ventas\CobrarProductos;
use Supermercado\Application\Ventas\CobrarRequest;
use Supermercado\Application\Ventas\ItemRequest;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Stock\AlertaDeStockRepository;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\MovimientoDeStock;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;
use Supermercado\Domain\Ventas\MetodoDePago;

uses(RefreshDatabase::class);

beforeEach(function () {
    fijarReloj('2026-01-15T10:00:00+00:00');
});

// ── Acceso ─────────────────────────────────────────────────────────────────

it('redirige al login sin autenticación', function () {
    $this->get('/tablero')->assertRedirect('/login');
});

it('renderiza el tablero para cada rol', function () {
    $this->actingAs(cajero())->get('/tablero')->assertOk();
    $this->actingAs(depositista())->get('/tablero')->assertOk();
    $this->actingAs(repositor())->get('/tablero')->assertOk();
});

// ── Use case: Cajero ───────────────────────────────────────────────────────

it('el tablero del cajero calcula KPIs de ventas del día', function () {
    app(ProductoRepository::class)->save(new Producto('p-1', 'Milk', new Dinero(1000, 'ARS')));
    app(GondolaRepository::class)->save(new Gondola('p-1', 40));
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));

    fijarReloj('2026-01-15T10:00:00+00:00');
    app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 's-1', cashierId: 'c-1', customerName: 'Alice',
        items: [new ItemRequest('p-1', 2)],
        metodoDePago: MetodoDePago::Efectivo,
    ));
    fijarReloj('2026-01-15T11:00:00+00:00');
    app(CobrarProductos::class)->execute(new CobrarRequest(
        saleId: 's-2', cashierId: 'c-1', customerName: 'Bob',
        items: [new ItemRequest('p-1', 1)],
        metodoDePago: MetodoDePago::TarjetaCredito,
    ));

    $view = app(ObtenerTableroCajero::class)->execute();

    expect($view->cantidadVentas)->toBe(2)
        ->and($view->totalVentas)->toBe(3000)
        ->and($view->ticketPromedio)->toBe(1500)
        ->and($view->moneda)->toBe('ARS')
        ->and($view->desglosePorMetodo)->toMatchArray(['efectivo' => 2000, 'tarjeta_credito' => 1000])
        ->and($view->ultimasVentas)->toHaveCount(2)
        ->and($view->ultimasVentas[0]['id'])->toBe('s-2')
        ->and($view->ultimasVentas[1]['id'])->toBe('s-1');
});

it('el tablero del cajero devuelve ceros sin ventas', function () {
    $view = app(ObtenerTableroCajero::class)->execute();

    expect($view->cantidadVentas)->toBe(0)
        ->and($view->totalVentas)->toBe(0)
        ->and($view->ticketPromedio)->toBe(0)
        ->and($view->ultimasVentas)->toBeEmpty()
        ->and($view->desglosePorMetodo)->toBeEmpty();
});

// ── Use case: Depositista ──────────────────────────────────────────────────

it('el tablero del depositista consolida alertas y movimientos', function () {
    app(AlertaDeStockRepository::class)->save(new AlertaDeStock(
        'p-1', UbicacionDeStock::Deposito, 120, new \DateTimeImmutable('2026-01-15 09:00:00'),
    ));
    app(AlertaDeStockRepository::class)->save(new AlertaDeStock(
        'p-2', UbicacionDeStock::Gondola, 25, new \DateTimeImmutable('2026-01-15 09:30:00'),
    ));
    app(MovimientoDeStockRepository::class)->save(new MovimientoDeStock(
        'm-1', 'p-1', TipoDeMovimiento::Reabastecimiento, 200,
        UbicacionDeStock::Deposito, new \DateTimeImmutable('2026-01-15 11:00:00'), 'Dist. SA',
    ));

    $view = app(ObtenerTableroDepositista::class)->execute();

    expect($view->alertasActivas)->toBe(2)
        ->and($view->alertasDeposito)->toBe(1)
        ->and($view->alertasGondola)->toBe(1)
        ->and($view->reabastecimientosHoy)->toBe(1)
        ->and($view->alertas)->toHaveCount(2)
        ->and($view->movimientosRecientes)->toHaveCount(1);
});

it('el tablero del depositista marca reabastecimientos de otro día como cero', function () {
    app(MovimientoDeStockRepository::class)->save(new MovimientoDeStock(
        'm-1', 'p-1', TipoDeMovimiento::Reabastecimiento, 200,
        UbicacionDeStock::Deposito, new \DateTimeImmutable('2026-01-14 11:00:00'), 'Dist. SA',
    ));

    $view = app(ObtenerTableroDepositista::class)->execute();

    expect($view->reabastecimientosHoy)->toBe(0);
});

// ── Use case: Repositor ────────────────────────────────────────────────────

it('el tablero del repositor filtra stock crítico', function () {
    app(GondolaRepository::class)->save(new Gondola('p-1', 25));
    app(GondolaRepository::class)->save(new Gondola('p-2', 50));
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));
    app(DepositoRepository::class)->save(new Deposito('p-2', 100));

    $view = app(ObtenerTableroRepositor::class)->execute();

    expect($view->productosGondolaBaja)->toBe(1)
        ->and($view->productosDepositoBajo)->toBe(1)
        ->and($view->totalProductos)->toBe(2)
        ->and($view->stockCritico)->toHaveCount(2);
});

it('el tablero del repositor sin stock crítico', function () {
    app(GondolaRepository::class)->save(new Gondola('p-1', 50));
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));

    $view = app(ObtenerTableroRepositor::class)->execute();

    expect($view->productosGondolaBaja)->toBe(0)
        ->and($view->productosDepositoBajo)->toBe(0)
        ->and($view->stockCritico)->toBeEmpty();
});
