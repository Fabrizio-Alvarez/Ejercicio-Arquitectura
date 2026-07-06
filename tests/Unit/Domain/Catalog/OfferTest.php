<?php

use Supermarket\Domain\Catalog\Offer;
use Supermarket\Domain\Shared\Money;

describe('Offer value object', function () {
    it('is active only within its validity window', function () {
        $offer = new Offer(
            'p-1',
            25.0,
            new \DateTimeImmutable('2026-01-01 00:00:00'),
            new \DateTimeImmutable('2026-01-31 23:59:59'),
        );

        expect($offer->isActive(new \DateTimeImmutable('2026-01-15 12:00:00')))->toBeTrue()
            ->and($offer->isActive(new \DateTimeImmutable('2026-01-01 00:00:00')))->toBeTrue()  // inclusive start
            ->and($offer->isActive(new \DateTimeImmutable('2026-01-31 23:59:59')))->toBeTrue()  // inclusive end
            ->and($offer->isActive(new \DateTimeImmutable('2025-12-31 23:59:59')))->toBeFalse()
            ->and($offer->isActive(new \DateTimeImmutable('2026-02-01 00:00:00')))->toBeFalse();
    });

    it('applies its discount to a price', function () {
        $offer = new Offer('p-1', 25.0, new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-01-31'));

        // 25% off 1000 cents = 750 cents
        expect($offer->applyTo(new Money(1000, 'ARS'))->amount())->toBe(750);
    });

    it('covers only its own product', function () {
        $offer = new Offer('p-1', 10.0, new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-01-31'));

        expect($offer->covers('p-1'))->toBeTrue()
            ->and($offer->covers('p-2'))->toBeFalse();
    });

    it('rejects a percent outside the 0..100 range', function () {
        expect(fn () => new Offer('p-1', 150.0, new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-01-31')))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn () => new Offer('p-1', -5.0, new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-01-31')))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('rejects a validity window where validTo is earlier than validFrom', function () {
        expect(fn () => new Offer(
            'p-1',
            10.0,
            new \DateTimeImmutable('2026-01-31'),
            new \DateTimeImmutable('2026-01-01'),
        ))->toThrow(\InvalidArgumentException::class);
    });
});
