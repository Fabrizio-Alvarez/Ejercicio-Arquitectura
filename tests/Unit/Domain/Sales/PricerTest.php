<?php

use Supermarket\Domain\Catalog\Offer;
use Supermarket\Domain\Catalog\Product;
use Supermarket\Domain\Sales\Pricer;
use Supermarket\Domain\Shared\Money;

describe('Pricer domain service', function () {
    it('prices a product at full price when no offer applies', function () {
        $pricer = new Pricer();
        $product = new Product('p-1', 'Milk', new Money(1000, 'ARS'));

        $line = $pricer->price($product, 2, [], new \DateTimeImmutable('2026-01-15'));

        expect($line->unitPrice())->toEqual(new Money(1000, 'ARS'))
            ->and($line->quantity())->toBe(2)
            ->and($line->total())->toEqual(new Money(2000, 'ARS'));
    });

    it('applies an active offer that covers the product', function () {
        $pricer = new Pricer();
        $product = new Product('p-1', 'Milk', new Money(1000, 'ARS'));
        $offer = new Offer('p-1', 25.0, new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-01-31'));

        $line = $pricer->price($product, 1, [$offer], new \DateTimeImmutable('2026-01-15'));

        expect($line->unitPrice())->toEqual(new Money(750, 'ARS')); // 25% off
    });

    it('ignores an offer that is not yet or no longer active', function () {
        $pricer = new Pricer();
        $product = new Product('p-1', 'Milk', new Money(1000, 'ARS'));
        $expired = new Offer('p-1', 25.0, new \DateTimeImmutable('2025-01-01'), new \DateTimeImmutable('2025-01-31'));

        $line = $pricer->price($product, 1, [$expired], new \DateTimeImmutable('2026-01-15'));

        expect($line->unitPrice())->toEqual(new Money(1000, 'ARS')); // no discount
    });

    it('picks the best (highest) offer when several are active', function () {
        $pricer = new Pricer();
        $product = new Product('p-1', 'Milk', new Money(1000, 'ARS'));

        $offers = [
            new Offer('p-1', 10.0, new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-01-31')),
            new Offer('p-1', 25.0, new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-01-31')),
            new Offer('p-1', 5.0, new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-01-31')),
        ];

        $line = $pricer->price($product, 1, $offers, new \DateTimeImmutable('2026-01-15'));

        expect($line->unitPrice())->toEqual(new Money(750, 'ARS')); // 25% wins
    });

    it('ignores offers that cover other products', function () {
        $pricer = new Pricer();
        $product = new Product('p-1', 'Milk', new Money(1000, 'ARS'));
        $offerForOther = new Offer('p-2', 50.0, new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-01-31'));

        $line = $pricer->price($product, 1, [$offerForOther], new \DateTimeImmutable('2026-01-15'));

        expect($line->unitPrice())->toEqual(new Money(1000, 'ARS'));
    });
});
