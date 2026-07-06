<?php

use Supermarket\Domain\Catalog\Product;
use Supermarket\Domain\Shared\Money;

describe('Product entity', function () {
    it('is constructed with id, name and a Money price', function () {
        $product = new Product('p-1', 'Milk 1L', new Money(150, 'ARS'));

        expect($product->id())->toBe('p-1')
            ->and($product->name())->toBe('Milk 1L')
            ->and($product->price())->toEqual(new Money(150, 'ARS'));
    });

    it('has identity equality by id, regardless of other attributes', function () {
        $a = new Product('p-1', 'Milk', new Money(150, 'ARS'));
        $b = new Product('p-1', 'Milk 2L', new Money(250, 'ARS'));
        $c = new Product('p-2', 'Milk', new Money(150, 'ARS'));

        expect($a->equals($b))->toBeTrue()
            ->and($a->equals($c))->toBeFalse();
    });

    it('allows renaming and repricing as mutable state', function () {
        $product = new Product('p-1', 'Milk', new Money(150, 'ARS'));

        $product->rename('Milk 1L');
        $product->changePrice(new Money(180, 'ARS'));

        expect($product->name())->toBe('Milk 1L')
            ->and($product->price())->toEqual(new Money(180, 'ARS'));
    });
});
