<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Application\Catalogo\ActualizarProducto;
use Supermercado\Application\Catalogo\CrearOferta;
use Supermercado\Application\Catalogo\CrearProducto;
use Supermercado\Application\Catalogo\EliminarProducto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Infrastructure\Persistence\OfertaModel;

uses(RefreshDatabase::class);

// ── Use cases ──────────────────────────────────────────────────────────────

it('crea un producto nuevo', function () {
    $p = app(CrearProducto::class)->execute('p-99', 'Café', 2500, 'ARS');

    expect($p->id())->toBe('p-99')
        ->and($p->name())->toBe('Café')
        ->and($p->price()->amount())->toBe(2500);
});

it('rechaza crear un producto con id duplicado', function () {
    app(CrearProducto::class)->execute('p-99', 'Café', 2500, 'ARS');

    app(CrearProducto::class)->execute('p-99', 'Otro', 1000, 'ARS');
})->throws(\DomainException::class);

it('actualiza nombre y precio de un producto', function () {
    app(CrearProducto::class)->execute('p-1', 'Leche', 1000, 'ARS');

    app(ActualizarProducto::class)->execute('p-1', 'Leche entera', 1200, 'ARS');

    $p = app(ProductoRepository::class)->find('p-1');
    expect($p->name())->toBe('Leche entera')
        ->and($p->price()->amount())->toBe(1200);
});

it('elimina un producto', function () {
    app(CrearProducto::class)->execute('p-1', 'Leche', 1000, 'ARS');

    app(EliminarProducto::class)->execute('p-1');

    expect(app(ProductoRepository::class)->find('p-1'))->toBeNull();
});

it('crea una oferta', function () {
    app(CrearProducto::class)->execute('p-1', 'Leche', 1000, 'ARS');

    $oferta = app(CrearOferta::class)->execute('p-1', 20.0, '2026-01-01 00:00:00', '2026-12-31 23:59:59');

    expect($oferta->percent())->toBe(20.0)
        ->and(OfertaModel::count())->toBe(1);
});

// ── API ────────────────────────────────────────────────────────────────────

it('crea un producto vía POST /api/products', function () {
    $this->actingAs(depositista())
        ->postJson('/api/products', ['id' => 'p-1', 'nombre' => 'Leche', 'precio' => 10.00, 'moneda' => 'ARS'])
        ->assertCreated()
        ->assertJsonPath('id', 'p-1');
});

it('actualiza un producto vía PUT /api/products/{id}', function () {
    app(CrearProducto::class)->execute('p-1', 'Leche', 1000, 'ARS');

    $this->actingAs(depositista())
        ->putJson('/api/products/p-1', ['nombre' => 'Leche entera', 'precio' => 12.00, 'moneda' => 'ARS'])
        ->assertOk();
});

it('elimina un producto vía DELETE /api/products/{id}', function () {
    app(CrearProducto::class)->execute('p-1', 'Leche', 1000, 'ARS');

    $this->actingAs(depositista())
        ->deleteJson('/api/products/p-1')
        ->assertOk();
});

it('rechaza producto con precio inválido (422)', function () {
    $this->actingAs(depositista())
        ->postJson('/api/products', ['id' => 'p-1', 'nombre' => 'X', 'precio' => -5, 'moneda' => 'ARS'])
        ->assertStatus(422);
});

it('rechaza el cajero en endpoints de catálogo (403)', function () {
    $this->actingAs(cajero())
        ->postJson('/api/products', ['id' => 'p-1', 'nombre' => 'X', 'precio' => 10, 'moneda' => 'ARS'])
        ->assertStatus(403);
});

it('lista productos vía GET /api/products', function () {
    app(CrearProducto::class)->execute('p-1', 'Leche', 1000, 'ARS');

    $this->actingAs(depositista())
        ->getJson('/api/products')
        ->assertOk()
        ->assertJsonPath('0.id', 'p-1');
});

// ── Web ────────────────────────────────────────────────────────────────────

it('renderiza la página de catálogo (depositista)', function () {
    $this->actingAs(depositista())->get('/catalogo')->assertOk();
});

it('el cajero no puede ver el catálogo', function () {
    $this->actingAs(cajero())->get('/catalogo')->assertRedirect('/tablero');
});
