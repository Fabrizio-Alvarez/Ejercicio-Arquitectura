<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Stock\RegistrarAjuste;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;

uses(RefreshDatabase::class);

function sembrarStock(string $id = 'p-1', int $gondola = 50, int $deposito = 500): void
{
    app(GondolaRepository::class)->save(new Gondola($id, $gondola));
    app(DepositoRepository::class)->save(new Deposito($id, $deposito));
}

// ── Use cases ──────────────────────────────────────────────────────────────

it('suma stock a la góndola con un ajuste positivo', function () {
    sembrarStock();

    app(RegistrarAjuste::class)->execute('p-1', UbicacionDeStock::Gondola, 10, 'Conteo');

    expect(app(GondolaRepository::class)->find('p-1')->quantity())->toBe(60)
        ->and(app(DepositoRepository::class)->find('p-1')->quantity())->toBe(500);
});

it('resta stock del depósito con un ajuste negativo', function () {
    sembrarStock();

    app(RegistrarAjuste::class)->execute('p-1', UbicacionDeStock::Deposito, -20, 'Merma');

    expect(app(DepositoRepository::class)->find('p-1')->quantity())->toBe(480);
});

it('resta stock de la góndola con un ajuste negativo', function () {
    sembrarStock();

    app(RegistrarAjuste::class)->execute('p-1', UbicacionDeStock::Gondola, -5, 'Rotura');

    expect(app(GondolaRepository::class)->find('p-1')->quantity())->toBe(45);
});

it('registra un movimiento de tipo Ajuste con el motivo como referencia', function () {
    sembrarStock();

    $mov = app(RegistrarAjuste::class)->execute('p-1', UbicacionDeStock::Deposito, 30, 'Donación');

    expect($mov->tipo())->toBe(TipoDeMovimiento::Ajuste)
        ->and($mov->cantidad())->toBe(30)
        ->and($mov->referencia())->toBe('Donación');

    $movs = app(MovimientoDeStockRepository::class)->findByProducto('p-1');
    expect($movs)->toHaveCount(1);
});

it('rechaza un ajuste con delta cero', function () {
    sembrarStock();

    app(RegistrarAjuste::class)->execute('p-1', UbicacionDeStock::Deposito, 0);
})->throws(\DomainException::class);

it('lanza si no hay stock rastreado para el producto', function () {
    app(RegistrarAjuste::class)->execute('p-99', UbicacionDeStock::Gondola, 10);
})->throws(\DomainException::class);

// ── API ────────────────────────────────────────────────────────────────────

it('registra un ajuste vía POST /api/adjust/{productId}', function () {
    sembrarStock();

    $this->actingAs(depositista())
        ->postJson('/api/adjust/p-1', ['ubicacion' => 'gondola', 'delta' => 5, 'motivo' => 'Conteo'])
        ->assertOk();

    expect(app(GondolaRepository::class)->find('p-1')->quantity())->toBe(55);
});

it('rechaza el repositor en el endpoint de ajuste (403)', function () {
    sembrarStock();

    $this->actingAs(repositor())
        ->postJson('/api/adjust/p-1', ['ubicacion' => 'gondola', 'delta' => 5])
        ->assertStatus(403);
});

it('rechaza un ajuste con ubicación inválida (422)', function () {
    sembrarStock();

    $this->actingAs(depositista())
        ->postJson('/api/adjust/p-1', ['ubicacion' => 'noexiste', 'delta' => 5])
        ->assertStatus(422);
});
