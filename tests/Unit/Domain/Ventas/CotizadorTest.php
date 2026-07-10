<?php

use Supermercado\Domain\Catalogo\Oferta;
use Supermercado\Domain\Catalogo\Ofertas;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Ventas\Cotizador;

describe('Cotizador domain service', function () {
    it('prices a product at full price when no offer applies', function () {
        $pricer = new Cotizador;
        $product = new Producto('p-1', 'Milk', new Dinero(1000, 'ARS'));

        $line = $pricer->price($product, 2, new Ofertas, new DateTimeImmutable('2026-01-15'));

        expect($line->unitPrice())->toEqual(new Dinero(1000, 'ARS'))
            ->and($line->quantity())->toBe(2)
            ->and($line->total())->toEqual(new Dinero(2000, 'ARS'));
    });

    it('applies an active offer that covers the product', function () {
        $pricer = new Cotizador;
        $product = new Producto('p-1', 'Milk', new Dinero(1000, 'ARS'));
        $offer = new Oferta('p-1', 25.0, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-01-31'));

        $line = $pricer->price($product, 1, new Ofertas([$offer]), new DateTimeImmutable('2026-01-15'));

        expect($line->unitPrice())->toEqual(new Dinero(750, 'ARS')); // 25% off
    });

    it('ignores an offer that is not yet or no longer active', function () {
        $pricer = new Cotizador;
        $product = new Producto('p-1', 'Milk', new Dinero(1000, 'ARS'));
        $expired = new Oferta('p-1', 25.0, new DateTimeImmutable('2025-01-01'), new DateTimeImmutable('2025-01-31'));

        $line = $pricer->price($product, 1, new Ofertas([$expired]), new DateTimeImmutable('2026-01-15'));

        expect($line->unitPrice())->toEqual(new Dinero(1000, 'ARS')); // no discount
    });

    it('picks the best (highest) offer when several are active', function () {
        $pricer = new Cotizador;
        $product = new Producto('p-1', 'Milk', new Dinero(1000, 'ARS'));

        $offers = new Ofertas([
            new Oferta('p-1', 10.0, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-01-31')),
            new Oferta('p-1', 25.0, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-01-31')),
            new Oferta('p-1', 5.0, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-01-31')),
        ]);

        $line = $pricer->price($product, 1, $offers, new DateTimeImmutable('2026-01-15'));

        expect($line->unitPrice())->toEqual(new Dinero(750, 'ARS')); // 25% wins
    });

    it('ignores offers that cover other products', function () {
        $pricer = new Cotizador;
        $product = new Producto('p-1', 'Milk', new Dinero(1000, 'ARS'));
        $offerForOther = new Oferta('p-2', 50.0, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-01-31'));

        $line = $pricer->price($product, 1, new Ofertas([$offerForOther]), new DateTimeImmutable('2026-01-15'));

        expect($line->unitPrice())->toEqual(new Dinero(1000, 'ARS'));
    });
});
