<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Stock\ConfigurarUmbrales;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;

uses(RefreshDatabase::class);

function sembrarStockConUmbrales(
    string $id = 'p-1',
    int $gondola = 50,
    int $deposito = 500,
    int $umbralGondola = 30,
    int $umbralDeposito = 150,
): void {
    app(GondolaRepository::class)->save(new Gondola($id, $gondola, $umbralGondola));
    app(DepositoRepository::class)->save(new Deposito($id, $deposito, $umbralDeposito));
}

// ── Use cases ──────────────────────────────────────────────────────────────

it('actualiza el umbral de góndola', function () {
    sembrarStockConUmbrales();

    app(ConfigurarUmbrales::class)->execute('p-1', 15, null);

    expect(app(GondolaRepository::class)->find('p-1')->umbralBajo())->toBe(15)
        ->and(app(DepositoRepository::class)->find('p-1')->umbralBajo())->toBe(150);
});

it('actualiza el umbral de depósito', function () {
    sembrarStockConUmbrales();

    app(ConfigurarUmbrales::class)->execute('p-1', null, 80);

    expect(app(GondolaRepository::class)->find('p-1')->umbralBajo())->toBe(30)
        ->and(app(DepositoRepository::class)->find('p-1')->umbralBajo())->toBe(80);
});

it('actualiza ambos umbrales a la vez', function () {
    sembrarStockConUmbrales();

    app(ConfigurarUmbrales::class)->execute('p-1', 10, 200);

    expect(app(GondolaRepository::class)->find('p-1')->umbralBajo())->toBe(10)
        ->and(app(DepositoRepository::class)->find('p-1')->umbralBajo())->toBe(200);
});

it('un umbral más alto hace que el stock actual se considere bajo', function () {
    sembrarStockConUmbrales('p-1', 50, 500); // gondola=50, umbral=30 → not low

    expect(app(GondolaRepository::class)->find('p-1')->isLow())->toBeFalse();

    app(ConfigurarUmbrales::class)->execute('p-1', 60, null); // umbral → 60

    expect(app(GondolaRepository::class)->find('p-1')->isLow())->toBeTrue();
});

it('un umbral cero desactiva la alerta', function () {
    sembrarStockConUmbrales('p-1', 0, 0); // qty=0, umbral default → low

    expect(app(GondolaRepository::class)->find('p-1')->isLow())->toBeTrue();

    app(ConfigurarUmbrales::class)->execute('p-1', 0, null);

    expect(app(GondolaRepository::class)->find('p-1')->isLow())->toBeFalse();
});

it('persiste el umbral personalizado a través del repositorio', function () {
    sembrarStockConUmbrales();

    app(ConfigurarUmbrales::class)->execute('p-1', 42, 77);

    $gondola = app(GondolaRepository::class)->find('p-1');
    $deposito = app(DepositoRepository::class)->find('p-1');

    expect($gondola->umbralBajo())->toBe(42)
        ->and($deposito->umbralBajo())->toBe(77);
});

it('lanza si no hay stock rastreado para el producto', function () {
    app(ConfigurarUmbrales::class)->execute('p-99', 10, null);
})->throws(\DomainException::class);

// ── API ────────────────────────────────────────────────────────────────────

it('configura umbrales vía PUT /api/threshold/{productId}', function () {
    sembrarStockConUmbrales();

    $this->actingAs(depositista())
        ->putJson('/api/threshold/p-1', ['umbral_gondola' => 20, 'umbral_deposito' => 100])
        ->assertOk();

    expect(app(GondolaRepository::class)->find('p-1')->umbralBajo())->toBe(20)
        ->and(app(DepositoRepository::class)->find('p-1')->umbralBajo())->toBe(100);
});

it('rechaza el repositor en el endpoint de umbrales (403)', function () {
    sembrarStockConUmbrales();

    $this->actingAs(repositor())
        ->putJson('/api/threshold/p-1', ['umbral_gondola' => 20])
        ->assertStatus(403);
});

it('rechaza un umbral negativo (422)', function () {
    sembrarStockConUmbrales();

    $this->actingAs(depositista())
        ->putJson('/api/threshold/p-1', ['umbral_gondola' => -5])
        ->assertStatus(422);
});
