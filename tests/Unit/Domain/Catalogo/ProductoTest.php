<?php

use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Comun\Dinero;

describe('Producto entity', function () {
    it('is constructed with id, name and a Dinero price', function () {
        $product = new Producto('p-1', 'Milk 1L', new Dinero(150, 'ARS'));

        expect($product->id())->toBe('p-1')
            ->and($product->name())->toBe('Milk 1L')
            ->and($product->price())->toEqual(new Dinero(150, 'ARS'));
    });

    it('has identity equality by id, regardless of other attributes', function () {
        $a = new Producto('p-1', 'Milk', new Dinero(150, 'ARS'));
        $b = new Producto('p-1', 'Milk 2L', new Dinero(250, 'ARS'));
        $c = new Producto('p-2', 'Milk', new Dinero(150, 'ARS'));

        expect($a->equals($b))->toBeTrue()
            ->and($a->equals($c))->toBeFalse();
    });

    it('allows renaming and repricing as mutable state', function () {
        $product = new Producto('p-1', 'Milk', new Dinero(150, 'ARS'));

        $product->rename('Milk 1L');
        $product->changePrice(new Dinero(180, 'ARS'));

        expect($product->name())->toBe('Milk 1L')
            ->and($product->price())->toEqual(new Dinero(180, 'ARS'));
    });
});
