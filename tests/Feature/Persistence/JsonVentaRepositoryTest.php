<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Ventas\EstadoDeVenta;
use Supermercado\Domain\Ventas\LineaDeVenta;
use Supermercado\Domain\Ventas\MetodoDePago;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Infrastructure\Persistence\JsonVentaRepository;

uses(RefreshDatabase::class); // solo para que TestCase bootee la app; no toca DB.

beforeEach(function () {
    $this->dir = sys_get_temp_dir() . '/sm-json-' . uniqid();
    config(['supermercado.json_dir' => $this->dir]);
});

afterEach(function () {
    $archivos = glob($this->dir . '/*') ?: [];
    foreach ($archivos as $archivo) {
        unlink($archivo);
    }
    @rmdir($this->dir);
});

function crearVentaConfirmada(): Venta
{
    $venta = new Venta('s-1', 'cashier-1', 'Jane Doe', new \DateTimeImmutable('2026-01-15 10:00:00'));
    $venta->addLine(new LineaDeVenta('p-1', 'Milk', 2, new Dinero(150, 'ARS')));
    $venta->addLine(new LineaDeVenta('p-2', 'Bread', 1, new Dinero(300, 'ARS')));
    $venta->confirm();

    return $venta;
}

it('persiste y reconstituye una venta confirmada con sus líneas, total y estado', function () {
    $repo = new JsonVentaRepository();
    $original = crearVentaConfirmada();

    $repo->save($original);

    $encontrada = $repo->find('s-1');

    expect($encontrada)->not->toBeNull()
        ->and($encontrada->id())->toBe('s-1')
        ->and($encontrada->cashierId())->toBe('cashier-1')
        ->and($encontrada->customerName())->toBe('Jane Doe')
        ->and($encontrada->metodoDePago())->toBe(MetodoDePago::Efectivo)
        ->and($encontrada->status())->toBe(EstadoDeVenta::Confirmada)
        ->and($encontrada->createdAt()->format(\DateTimeInterface::ATOM))->toBe($original->createdAt()->format(\DateTimeInterface::ATOM))
        ->and($encontrada->lines())->toHaveCount(2)
        ->and($encontrada->total())->toEqual(new Dinero(600, 'ARS'))
        ->and($encontrada->itemCount())->toBe(3);
});

it('hidrata las líneas intactas: producto, cantidad y precio unitario', function () {
    $repo = new JsonVentaRepository();
    $repo->save(crearVentaConfirmada());

    $lineas = $repo->find('s-1')->lines();

    expect($lineas[0]->productId())->toBe('p-1')
        ->and($lineas[0]->productName())->toBe('Milk')
        ->and($lineas[0]->quantity())->toBe(2)
        ->and($lineas[0]->unitPrice())->toEqual(new Dinero(150, 'ARS'))
        ->and($lineas[0]->total())->toEqual(new Dinero(300, 'ARS'))
        ->and($lineas[1]->productId())->toBe('p-2')
        ->and($lineas[1]->productName())->toBe('Bread')
        ->and($lineas[1]->quantity())->toBe(1)
        ->and($lineas[1]->unitPrice())->toEqual(new Dinero(300, 'ARS'))
        ->and($lineas[1]->total())->toEqual(new Dinero(300, 'ARS'));
});

it('el total reconstituido iguala el total original', function () {
    $repo = new JsonVentaRepository();
    $original = crearVentaConfirmada();

    $repo->save($original);

    expect($repo->find('s-1')->total())->toEqual($original->total());
});

it('devuelve null para una venta inexistente', function () {
    $repo = new JsonVentaRepository();

    expect($repo->find('no-existe'))->toBeNull();
});

it('all() devuelve todas las ventas persistidas', function () {
    $repo = new JsonVentaRepository();

    foreach (['s-1', 's-2', 's-3'] as $id) {
        $venta = new Venta($id, 'cashier-1', 'Jane', new \DateTimeImmutable('2026-01-15'));
        $venta->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
        $venta->confirm();
        $repo->save($venta);
    }

    $todas = $repo->all();

    expect($todas)->toHaveCount(3)
        ->and(array_map(fn (Venta $v): string => $v->id(), $todas))->toEqual(['s-1', 's-2', 's-3']);
});

it('save de un id existente actualiza la venta (upsert)', function () {
    $repo = new JsonVentaRepository();

    // Primera versión: pendiente, 1 línea.
    $venta = new Venta('s-1', 'cashier-1', 'Jane', new \DateTimeImmutable('2026-01-15 10:00:00'));
    $venta->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
    $repo->save($venta);

    // Misma id, distinta composición: otro cajero/cliente, 2 líneas y estado confirmada.
    $actualizada = new Venta('s-1', 'cashier-2', 'John', new \DateTimeImmutable('2026-01-16 12:00:00'));
    $actualizada->addLine(new LineaDeVenta('p-2', 'Bread', 3, new Dinero(200, 'ARS')));
    $actualizada->addLine(new LineaDeVenta('p-3', 'Eggs', 2, new Dinero(100, 'ARS')));
    $actualizada->confirm();
    $repo->save($actualizada);

    // No debe duplicar; debe reflejar el último estado.
    expect($repo->all())->toHaveCount(1);

    $reconstituida = $repo->find('s-1');

    expect($reconstituida)->not->toBeNull()
        ->and($reconstituida->cashierId())->toBe('cashier-2')
        ->and($reconstituida->customerName())->toBe('John')
        ->and($reconstituida->status())->toBe(EstadoDeVenta::Confirmada)
        ->and($reconstituida->createdAt()->format(\DateTimeInterface::ATOM))->toBe($actualizada->createdAt()->format(\DateTimeInterface::ATOM))
        ->and($reconstituida->lines())->toHaveCount(2)
        ->and($reconstituida->total())->toEqual(new Dinero(800, 'ARS'))
        ->and($reconstituida->itemCount())->toBe(5);
});

it('persiste correctamente con un método de pago distinto', function () {
    $repo = new JsonVentaRepository();

    $venta = new Venta(
        's-9',
        'cashier-1',
        'Jane',
        new \DateTimeImmutable('2026-01-15 10:00:00'),
        MetodoDePago::TarjetaCredito,
    );
    $venta->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
    $venta->confirm();
    $repo->save($venta);

    expect($repo->find('s-9')->metodoDePago())->toBe(MetodoDePago::TarjetaCredito);
});
