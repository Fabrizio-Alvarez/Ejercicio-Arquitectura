<?php

use Supermercado\Domain\Stock\Gondola;

describe('Gondola (góndola)', function () {
    it('holds a non-negative quantity per product', function () {
        $shelf = new Gondola('p-1', 45);

        expect($shelf->productId())->toBe('p-1')
            ->and($shelf->quantity())->toBe(45);
    });

    it('rejects a negative initial quantity', function () {
        expect(fn () => new Gondola('p-1', -1))->toThrow(\InvalidArgumentException::class);
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
});
