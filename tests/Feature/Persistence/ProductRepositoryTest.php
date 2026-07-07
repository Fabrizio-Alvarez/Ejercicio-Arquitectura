<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermarket\Domain\Catalog\Product;
use Supermarket\Domain\Catalog\ProductRepository;
use Supermarket\Domain\Shared\Money;

uses(RefreshDatabase::class);

it('persists and retrieves a product through the repository', function () {
    $repo = app(ProductRepository::class);

    $repo->save(new Product('p-1', 'Milk 1L', new Money(150, 'ARS')));

    $found = $repo->find('p-1');

    expect($found)->not->toBeNull()
        ->and($found->name())->toBe('Milk 1L')
        ->and($found->price())->toEqual(new Money(150, 'ARS'));
});

it('returns null for a missing product', function () {
    expect(app(ProductRepository::class)->find('nope'))->toBeNull();
});

it('updates a product when saved again with the same id', function () {
    $repo = app(ProductRepository::class);

    $repo->save(new Product('p-1', 'Milk', new Money(150, 'ARS')));
    $repo->save(new Product('p-1', 'Milk 1L', new Money(180, 'ARS')));

    $found = $repo->find('p-1');

    expect($found->name())->toBe('Milk 1L')
        ->and($found->price())->toEqual(new Money(180, 'ARS'));
});

it('lists all persisted products', function () {
    $repo = app(ProductRepository::class);

    $repo->save(new Product('p-1', 'Milk', new Money(150, 'ARS')));
    $repo->save(new Product('p-2', 'Bread', new Money(300, 'ARS')));

    expect($repo->all())->toHaveCount(2);
});
