<?php

// Pure domain test — runs WITHOUT booting Laravel.
// This file is the headline of the project: the domain is tested in isolation.

use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Comun\MonedaDistintaException;

describe('Dinero value object', function () {
    it('holds an amount in integer cents and a currency', function () {
        $money = new Dinero(1099, 'ARS');

        expect($money->amount())->toBe(1099)
            ->and($money->currency())->toBe('ARS');
    });

    it('parses a decimal string into cents without float drift', function () {
        // The classic float bug: 0.1 + 0.2 != 0.3. We avoid it by storing integer cents.
        expect(Dinero::fromDecimal('10.99', 'ARS')->amount())->toBe(1099)
            ->and(Dinero::fromDecimal('0.03', 'ARS')->amount())->toBe(3);
    });

    it('is immutable: add returns a new instance and leaves the original untouched', function () {
        $original = new Dinero(100, 'ARS');
        $result = $original->add(new Dinero(50, 'ARS'));

        expect($result)->not->toBe($original)
            ->and($result->amount())->toBe(150)
            ->and($original->amount())->toBe(100);
    });

    it('adds amounts of the same currency', function () {
        $sum = (new Dinero(100, 'USD'))->add(new Dinero(250, 'USD'));

        expect($sum->amount())->toBe(350)
            ->and($sum->currency())->toBe('USD');
    });

    it('refuses to operate across different currencies', function () {
        expect(fn () => (new Dinero(100, 'ARS'))->add(new Dinero(100, 'USD')))
            ->toThrow(MonedaDistintaException::class);
    });

    it('subtracts amounts of the same currency', function () {
        expect((new Dinero(500, 'ARS'))->subtract(new Dinero(120, 'ARS'))->amount())->toBe(380);
    });

    it('multiplies by an integer factor', function () {
        expect((new Dinero(1000, 'ARS'))->multiply(3)->amount())->toBe(3000);
    });

    it('applies a percentage discount and rounds half-up on the cent', function () {
        // 25% off 1000 cents = 750 cents
        expect((new Dinero(1000, 'ARS'))->applyPercent(25)->amount())->toBe(750);

        // 10% off 995 cents = 895.5 -> rounds to 896 (half-up)
        expect((new Dinero(995, 'ARS'))->applyPercent(10)->amount())->toBe(896);
    });

    it('compares by value AND currency', function () {
        expect((new Dinero(100, 'ARS'))->equals(new Dinero(100, 'ARS')))->toBeTrue()
            ->and((new Dinero(100, 'ARS'))->equals(new Dinero(100, 'USD')))->toBeFalse()
            ->and((new Dinero(100, 'ARS'))->equals(new Dinero(200, 'ARS')))->toBeFalse();
    });

    it('formats back to a decimal string', function () {
        expect((string) (new Dinero(1099, 'ARS')))->toBe('10.99 ARS');
    });
    it('sums a non-empty list of same-currency Dinero into one amount', function () {
        $sum = Dinero::sum(new Dinero(100, 'ARS'), new Dinero(250, 'ARS'), new Dinero(50, 'ARS'));

        expect($sum)->toEqual(new Dinero(400, 'ARS'))
            ->and($sum->currency())->toBe('ARS');
    });

    it('sum enforces the same currency across every term', function () {
        expect(fn () => Dinero::sum(new Dinero(100, 'ARS'), new Dinero(100, 'USD')))
            ->toThrow(MonedaDistintaException::class);
    });

});
