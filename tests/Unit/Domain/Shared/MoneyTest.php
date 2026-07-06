<?php

// Pure domain test — runs WITHOUT booting Laravel.
// This file is the headline of the project: the domain is tested in isolation.

use Supermarket\Domain\Shared\CurrencyMismatchException;
use Supermarket\Domain\Shared\Money;

describe('Money value object', function () {
    it('holds an amount in integer cents and a currency', function () {
        $money = new Money(1099, 'ARS');

        expect($money->amount())->toBe(1099)
            ->and($money->currency())->toBe('ARS');
    });

    it('parses a decimal string into cents without float drift', function () {
        // The classic float bug: 0.1 + 0.2 != 0.3. We avoid it by storing integer cents.
        expect(Money::fromDecimal('10.99', 'ARS')->amount())->toBe(1099)
            ->and(Money::fromDecimal('0.03', 'ARS')->amount())->toBe(3);
    });

    it('is immutable: add returns a new instance and leaves the original untouched', function () {
        $original = new Money(100, 'ARS');
        $result = $original->add(new Money(50, 'ARS'));

        expect($result)->not->toBe($original)
            ->and($result->amount())->toBe(150)
            ->and($original->amount())->toBe(100);
    });

    it('adds amounts of the same currency', function () {
        $sum = (new Money(100, 'USD'))->add(new Money(250, 'USD'));

        expect($sum->amount())->toBe(350)
            ->and($sum->currency())->toBe('USD');
    });

    it('refuses to operate across different currencies', function () {
        expect(fn () => (new Money(100, 'ARS'))->add(new Money(100, 'USD')))
            ->toThrow(CurrencyMismatchException::class);
    });

    it('subtracts amounts of the same currency', function () {
        expect((new Money(500, 'ARS'))->subtract(new Money(120, 'ARS'))->amount())->toBe(380);
    });

    it('multiplies by an integer factor', function () {
        expect((new Money(1000, 'ARS'))->multiply(3)->amount())->toBe(3000);
    });

    it('applies a percentage discount and rounds half-up on the cent', function () {
        // 25% off 1000 cents = 750 cents
        expect((new Money(1000, 'ARS'))->applyPercent(25)->amount())->toBe(750);

        // 10% off 995 cents = 895.5 -> rounds to 896 (half-up)
        expect((new Money(995, 'ARS'))->applyPercent(10)->amount())->toBe(896);
    });

    it('compares by value AND currency', function () {
        expect((new Money(100, 'ARS'))->equals(new Money(100, 'ARS')))->toBeTrue()
            ->and((new Money(100, 'ARS'))->equals(new Money(100, 'USD')))->toBeFalse()
            ->and((new Money(100, 'ARS'))->equals(new Money(200, 'ARS')))->toBeFalse();
    });

    it('formats back to a decimal string', function () {
        expect((string) (new Money(1099, 'ARS')))->toBe('10.99 ARS');
    });
});
