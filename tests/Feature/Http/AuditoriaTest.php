<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Supermercado\Application\Auditoria\ListarEventos;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Stock\UbicacionDeStock;
use Supermercado\Domain\Ventas\CompraRealizada;
use Supermercado\Domain\Ventas\LineaDeVenta;
use Supermercado\Domain\Ventas\MetodoDePago;
use Supermercado\Infrastructure\Persistence\EventoDeDominioModel;

uses(RefreshDatabase::class);

it('persiste un evento CompraRealizada en el log de auditoría', function () {
    Event::dispatch(new CompraRealizada('v-1', MetodoDePago::Efectivo, [
        new LineaDeVenta('p-1', 'Leche', 2, new Dinero(1000, 'ARS')),
    ]));

    expect(EventoDeDominioModel::count())->toBe(1)
        ->and(EventoDeDominioModel::first()->tipo)->toBe('CompraRealizada')
        ->and(EventoDeDominioModel::first()->payload['ventaId'])->toBe('v-1');
});

it('persiste un evento AlertaDeStock en el log de auditoría', function () {
    Event::dispatch(new AlertaDeStock('p-1', UbicacionDeStock::Deposito, 80, new \DateTimeImmutable()));

    expect(EventoDeDominioModel::count())->toBe(1)
        ->and(EventoDeDominioModel::first()->tipo)->toBe('AlertaDeStock')
        ->and(EventoDeDominioModel::first()->payload['productoId'])->toBe('p-1');
});

it('persiste múltiples eventos y los ordena por fecha descendente', function () {
    Event::dispatch(new AlertaDeStock('p-1', UbicacionDeStock::Deposito, 80, new \DateTimeImmutable()));
    \usleep(10000);
    Event::dispatch(new CompraRealizada('v-1', MetodoDePago::Efectivo, [
        new LineaDeVenta('p-1', 'Leche', 2, new Dinero(1000, 'ARS')),
    ]));

    $eventos = app(ListarEventos::class)->execute();

    expect($eventos)->toHaveCount(2)
        ->and($eventos[0]->tipo)->toBe('CompraRealizada')
        ->and($eventos[1]->tipo)->toBe('AlertaDeStock');
});

it('renderiza la página de auditoría (depositista)', function () {
    $this->actingAs(depositista())->get('/auditoria')->assertOk();
});

it('el repositor no puede ver la auditoría', function () {
    $this->actingAs(repositor())->get('/auditoria')->assertRedirect('/tablero');
});
