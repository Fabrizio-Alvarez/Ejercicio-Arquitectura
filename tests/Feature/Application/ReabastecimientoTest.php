<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Stock\RegistrarReabastecimiento;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;

uses(RefreshDatabase::class);

// ── Use case ──────────────────────────────────────────────────────────────

it('reabastece el depósito y deja huella del movimiento con proveedor', function () {
    app(DepositoRepository::class)->save(new Deposito('p-1', 130)); // bajo (< 150)

    $resultado = app(RegistrarReabastecimiento::class)->execute('p-1', 200, 'Distribuidora SA');

    expect($resultado->recibido)->toBe(200)
        ->and($resultado->nivelDelDeposito)->toBe(330)
        ->and(app(DepositoRepository::class)->find('p-1')->quantity())->toBe(330);

    $movimientos = app(MovimientoDeStockRepository::class)->findByProducto('p-1');
    expect($movimientos)->toHaveCount(1)
        ->and($movimientos[0]->tipo())->toBe(TipoDeMovimiento::Reabastecimiento)
        ->and($movimientos[0]->cantidad())->toBe(200)
        ->and($movimientos[0]->ubicacion())->toBe(UbicacionDeStock::Deposito)
        ->and($movimientos[0]->referencia())->toBe('Distribuidora SA');
});

it('reabastece sin proveedor (referencia null)', function () {
    app(DepositoRepository::class)->save(new Deposito('p-1', 130));

    app(RegistrarReabastecimiento::class)->execute('p-1', 50);

    $movimientos = app(MovimientoDeStockRepository::class)->findByProducto('p-1');
    expect($movimientos[0]->referencia())->toBeNull();
});

it('rechaza reabastecer un producto sin stock trackeado', function () {
    app(RegistrarReabastecimiento::class)->execute('inexistente', 100);
})->throws(\DomainException::class);

it('rechaza una cantidad no positiva', function () {
    app(DepositoRepository::class)->save(new Deposito('p-1', 130));

    app(RegistrarReabastecimiento::class)->execute('p-1', 0);
})->throws(\DomainException::class);

// ── API ───────────────────────────────────────────────────────────────────

it('reabastece vía POST /api/restock/{productId}', function () {
    $this->actingAs(depositista());
    app(DepositoRepository::class)->save(new Deposito('p-1', 130));

    $this->postJson('/api/restock/p-1', ['quantity' => 200, 'proveedor' => 'Dist. SA'])
        ->assertOk()
        ->assertJsonPath('recibido', 200)
        ->assertJsonPath('nivelDelDeposito', 330);

    expect(app(DepositoRepository::class)->find('p-1')->quantity())->toBe(330);
});

it('rechaza /api/restock con cantidad inválida (422)', function () {
    $this->actingAs(depositista());
    app(DepositoRepository::class)->save(new Deposito('p-1', 130));

    $this->postJson('/api/restock/p-1', ['quantity' => 0])->assertStatus(422);
});

it('rechaza /api/restock sin quantity (422)', function () {
    $this->actingAs(depositista());
    app(DepositoRepository::class)->save(new Deposito('p-1', 130));

    $this->postJson('/api/restock/p-1', [])->assertStatus(422);
});

// ── CLI ───────────────────────────────────────────────────────────────────

it('reabastece vía artisan stock:restock', function () {
    app(DepositoRepository::class)->save(new Deposito('p-1', 130));

    $this->artisan('stock:restock', ['productId' => 'p-1', 'cantidad' => 200, '--proveedor' => 'Dist. SA'])
        ->assertSuccessful();

    expect(app(DepositoRepository::class)->find('p-1')->quantity())->toBe(330);
});

it('el CLI falla si el producto no está trackeado', function () {
    $this->artisan('stock:restock', ['productId' => 'x', 'cantidad' => 100])
        ->assertFailed();
});

// ── Flujo completo: alerta de depósito → reabastecer ──────────────────────

it('resuelve una alerta de depósito: reabastecer sube el nivel y deja de estar bajo', function () {
    // Estado que deja RegistrarReposicion cuando el depósito cae bajo 150.
    app(DepositoRepository::class)->save(new Deposito('p-1', 130));
    expect(app(DepositoRepository::class)->find('p-1')->isLow())->toBeTrue();

    app(RegistrarReabastecimiento::class)->execute('p-1', 500);

    expect(app(DepositoRepository::class)->find('p-1')->quantity())->toBe(630)
        ->and(app(DepositoRepository::class)->find('p-1')->isLow())->toBeFalse();
});
