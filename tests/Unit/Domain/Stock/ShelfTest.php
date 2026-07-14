<?php

use Supermercado\Domain\Stock\Gondola;

describe('Gondola (góndola)', function () {
    it('holds a non-negative quantity per product', function () {
        $shelf = new Gondola('p-1', 45);

        expect($shelf->productId())->toBe('p-1')
            ->and($shelf->quantity())->toBe(45);
    });

    it('rejects a negative initial quantity', function () {
        expect(fn () => new Gondola('p-1', -1))->toThrow(InvalidArgumentException::class);
    });

    it('increases quantity on restock', function () {
        $shelf = new Gondola('p-1', 20);
        $shelf->restock(30);

        expect($shelf->quantity())->toBe(50);
    });

    it('flags itself as low below the threshold of 30', function () {
        expect((new Gondola('p-1', 29))->isLow())->toBeTrue()
            ->and((new Gondola('p-1', 30))->isLow())->toBeFalse();
    });
    it('knows how many units are missing to reach a target level', function () {
        $shelf = new Gondola('p-1', 20);

        expect($shelf->gapTo(50))->toBe(30)    // 50 - 20
            ->and($shelf->gapTo(20))->toBe(0)  // already at target
            ->and($shelf->gapTo(10))->toBe(0); // never negative
    });


    it('reserves stock reducing disponible', function () {
        $shelf = new Gondola('p-1', 50);

        $shelf->reservar(10);

        expect($shelf->reservado())->toBe(10)
            ->and($shelf->disponible())->toBe(40)
            ->and($shelf->quantity())->toBe(50); // quantity unchanged
    });

    it('refuses to reserve more than disponible', function () {
        $shelf = new Gondola('p-1', 50);
        $shelf->reservar(40);

        expect(fn () => $shelf->reservar(20))->toThrow(DomainException::class);
    });

    it('confirmarReserva converts reservation to actual deduction', function () {
        $shelf = new Gondola('p-1', 50);
        $shelf->reservar(10);

        $shelf->confirmarReserva(10);

        expect($shelf->quantity())->toBe(40)
            ->and($shelf->reservado())->toBe(0)
            ->and($shelf->disponible())->toBe(40);
    });

    it('confirmarReserva without prior reservation works like descontar', function () {
        $shelf = new Gondola('p-1', 50);

        $shelf->confirmarReserva(10);

        expect($shelf->quantity())->toBe(40)
            ->and($shelf->reservado())->toBe(0);
    });

    it('liberarReserva releases stock without deducting', function () {
        $shelf = new Gondola('p-1', 50);
        $shelf->reservar(15);

        $shelf->liberarReserva(15);

        expect($shelf->quantity())->toBe(50)
            ->and($shelf->reservado())->toBe(0)
            ->and($shelf->disponible())->toBe(50);
    });
});
