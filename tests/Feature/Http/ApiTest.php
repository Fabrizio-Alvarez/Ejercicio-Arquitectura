<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\LineaDeVenta;
use Supermercado\Domain\Ventas\VentaRepository;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Infrastructure\Persistence\OfertaModel;

uses(RefreshDatabase::class);

it('checks out products with the active offer via POST /api/checkout', function () {
    app(ProductoRepository::class)->save(new Producto('p-1', 'Milk', new Dinero(1000, 'ARS')));
    OfertaModel::create([
        'product_id' => 'p-1',
        'percent' => 25.00,
        'valid_from' => '2020-01-01 00:00:00',
        'valid_to' => '2099-12-31 23:59:59',
    ]);

    $this->postJson('/api/checkout', [
        'saleId' => 's-1',
        'cashierId' => 'cashier-1',
        'customerName' => 'Jane Doe',
        'paymentMethod' => 'tarjeta_credito',
        'items' => [['productId' => 'p-1', 'quantity' => 2]],
    ])
        ->assertCreated()
        ->assertJsonPath('total.amount', 1500)
        ->assertJsonPath('total.currency', 'ARS')
        ->assertJsonPath('status', 'confirmada')
        ->assertJsonPath('paymentMethod', 'tarjeta_credito');
});

it('rejects an invalid checkout payload with 422', function () {
    $this->postJson('/api/checkout', [])->assertStatus(422);
});

it('lists stock via GET /api/stock', function () {
    app(GondolaRepository::class)->save(new Gondola('p-1', 45));
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));

    $this->getJson('/api/stock')
        ->assertOk()
        ->assertJsonPath('items.0.productId', 'p-1')
        ->assertJsonPath('items.0.shelfQuantity', 45)
        ->assertJsonPath('items.0.warehouseQuantity', 500);
});

it('replenishes a low shelf via POST /api/replenish/{productId}', function () {
    app(GondolaRepository::class)->save(new Gondola('p-1', 20));
    app(DepositoRepository::class)->save(new Deposito('p-1', 500));

    $this->postJson('/api/replenish/p-1')
        ->assertOk()
        ->assertJsonPath('moved', 30)
        ->assertJsonPath('alert', null);
});

it('returns the cash close via GET /api/cash-close', function () {
    $sale = new Venta('s-1', 'cashier-1', 'Jane', new \DateTimeImmutable('2026-01-15 10:00:00'));
    $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(450, 'ARS')));
    $sale->confirm();
    app(VentaRepository::class)->save($sale);

    $this->call('GET', '/api/cash-close', ['cashierId' => 'cashier-1', 'date' => '2026-01-15'])
        ->assertOk()
        ->assertJsonPath('count', 1)
        ->assertJsonPath('total.amount', 450);
});
