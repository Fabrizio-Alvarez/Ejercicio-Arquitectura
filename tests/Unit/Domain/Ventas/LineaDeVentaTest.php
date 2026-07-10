<?php

use Supermercado\Domain\Ventas\LineaDeVenta;
use Supermercado\Domain\Comun\Dinero;

describe('LineaDeVenta', function () {
    it('computes its total as unit price times quantity', function () {
        $line = new LineaDeVenta('p-1', 'Milk', 3, new Dinero(150, 'ARS'));

        expect($line->total())->toEqual(new Dinero(450, 'ARS'))
            ->and($line->currency())->toBe('ARS');
    });

    it('rejects a quantity below 1', function () {
        expect(fn () => new LineaDeVenta('p-1', 'Milk', 0, new Dinero(150, 'ARS')))
            ->toThrow(\InvalidArgumentException::class);
    });
});
