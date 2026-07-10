<?php

use Supermercado\Domain\Catalogo\Oferta;
use Supermercado\Domain\Catalogo\Ofertas;

describe('Ofertas (colección de ofertas)', function () {
    it('returns null when there are no offers', function () {
        expect((new Ofertas)->bestActiveFor('p-1', new DateTimeImmutable('2026-01-15')))->toBeNull();
    });

    it('picks the highest-percent active offer covering the product', function () {
        $from = new DateTimeImmutable('2026-01-01');
        $to = new DateTimeImmutable('2026-01-31');
        $ofertas = new Ofertas([
            new Oferta('p-1', 10.0, $from, $to),
            new Oferta('p-1', 25.0, $from, $to),
            new Oferta('p-1', 5.0, $from, $to),
        ]);

        $best = $ofertas->bestActiveFor('p-1', new DateTimeImmutable('2026-01-15'));

        expect($best)->not->toBeNull()
            ->and($best->percent())->toBe(25.0);
    });

    it('ignores inactive offers and offers for other products', function () {
        $ofertas = new Ofertas([
            new Oferta('p-1', 25.0, new DateTimeImmutable('2025-01-01'), new DateTimeImmutable('2025-01-31')), // expired
            new Oferta('p-2', 50.0, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-01-31')), // other product
        ]);

        expect($ofertas->bestActiveFor('p-1', new DateTimeImmutable('2026-01-15')))->toBeNull();
    });

    it('is countable and iterable', function () {
        $from = new DateTimeImmutable('2026-01-01');
        $to = new DateTimeImmutable('2026-01-31');
        $ofertas = new Ofertas([new Oferta('p-1', 10.0, $from, $to)]);

        expect(count($ofertas))->toBe(1)
            ->and(iterator_to_array($ofertas))->toHaveCount(1);
    });

    it('rejects non-Oferta elements defensively', function () {
        expect(fn () => new Ofertas(['not-an-offer']))->toThrow(InvalidArgumentException::class);
    });
});
