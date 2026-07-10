<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Dinero;

uses(RefreshDatabase::class);

it('persists and retrieves a product through the repository', function () {
    $repo = app(ProductoRepository::class);

    $repo->save(new Producto('p-1', 'Milk 1L', new Dinero(150, 'ARS')));

    $found = $repo->find('p-1');

    expect($found)->not->toBeNull()
        ->and($found->name())->toBe('Milk 1L')
        ->and($found->price())->toEqual(new Dinero(150, 'ARS'));
});

it('returns null for a missing product', function () {
    expect(app(ProductoRepository::class)->find('nope'))->toBeNull();
});

it('updates a product when saved again with the same id', function () {
    $repo = app(ProductoRepository::class);

    $repo->save(new Producto('p-1', 'Milk', new Dinero(150, 'ARS')));
    $repo->save(new Producto('p-1', 'Milk 1L', new Dinero(180, 'ARS')));

    $found = $repo->find('p-1');

    expect($found->name())->toBe('Milk 1L')
        ->and($found->price())->toEqual(new Dinero(180, 'ARS'));
});

it('lists all persisted products', function () {
    $repo = app(ProductoRepository::class);

    $repo->save(new Producto('p-1', 'Milk', new Dinero(150, 'ARS')));
    $repo->save(new Producto('p-2', 'Bread', new Dinero(300, 'ARS')));

    expect($repo->all())->toHaveCount(2);
});
