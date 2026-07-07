<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermarket\Domain\Catalog\Product;
use Supermarket\Domain\Catalog\ProductRepository;
use Supermarket\Domain\Sales\Sale;
use Supermarket\Domain\Sales\SaleLine;
use Supermarket\Domain\Sales\SaleRepository;
use Supermarket\Domain\Shared\Money;
use Supermarket\Domain\Stock\Shelf;
use Supermarket\Domain\Stock\ShelfRepository;
use Supermarket\Domain\Stock\Warehouse;
use Supermarket\Domain\Stock\WarehouseRepository;
use Supermarket\Infrastructure\Persistence\OfferModel;

uses(RefreshDatabase::class);

it('checks out products with the active offer via POST /api/checkout', function () {
    app(ProductRepository::class)->save(new Product('p-1', 'Milk', new Money(1000, 'ARS')));
    OfferModel::create([
        'product_id' => 'p-1',
        'percent' => 25.00,
        'valid_from' => '2020-01-01 00:00:00',
        'valid_to' => '2099-12-31 23:59:59',
    ]);

    $this->postJson('/api/checkout', [
        'saleId' => 's-1',
        'cashierId' => 'cashier-1',
        'customerName' => 'Jane Doe',
        'items' => [['productId' => 'p-1', 'quantity' => 2]],
    ])
        ->assertCreated()
        ->assertJsonPath('total.amount', 1500)
        ->assertJsonPath('total.currency', 'ARS')
        ->assertJsonPath('status', 'confirmed');
});

it('rejects an invalid checkout payload with 422', function () {
    $this->postJson('/api/checkout', [])->assertStatus(422);
});

it('lists stock via GET /api/stock', function () {
    app(ShelfRepository::class)->save(new Shelf('p-1', 45));
    app(WarehouseRepository::class)->save(new Warehouse('p-1', 500));

    $this->getJson('/api/stock')
        ->assertOk()
        ->assertJsonPath('items.0.productId', 'p-1')
        ->assertJsonPath('items.0.shelfQuantity', 45)
        ->assertJsonPath('items.0.warehouseQuantity', 500);
});

it('replenishes a low shelf via POST /api/replenish/{productId}', function () {
    app(ShelfRepository::class)->save(new Shelf('p-1', 20));
    app(WarehouseRepository::class)->save(new Warehouse('p-1', 500));

    $this->postJson('/api/replenish/p-1')
        ->assertOk()
        ->assertJsonPath('moved', 30)
        ->assertJsonPath('alert', null);
});

it('returns the cash close via GET /api/cash-close', function () {
    $sale = new Sale('s-1', 'cashier-1', 'Jane', new \DateTimeImmutable('2026-01-15 10:00:00'));
    $sale->addLine(new SaleLine('p-1', 'Milk', 1, new Money(450, 'ARS')));
    $sale->confirm();
    app(SaleRepository::class)->save($sale);

    $this->call('GET', '/api/cash-close', ['cashierId' => 'cashier-1', 'date' => '2026-01-15'])
        ->assertOk()
        ->assertJsonPath('count', 1)
        ->assertJsonPath('total.amount', 450);
});
