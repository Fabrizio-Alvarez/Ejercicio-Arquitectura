<?php

use Supermarket\Domain\Stock\Shelf;

describe('Shelf (góndola)', function () {
    it('holds a non-negative quantity per product', function () {
        $shelf = new Shelf('p-1', 45);

        expect($shelf->productId())->toBe('p-1')
            ->and($shelf->quantity())->toBe(45);
    });

    it('rejects a negative initial quantity', function () {
        expect(fn () => new Shelf('p-1', -1))->toThrow(\InvalidArgumentException::class);
    });

    it('increases quantity on restock', function () {
        $shelf = new Shelf('p-1', 20);
        $shelf->restock(30);

        expect($shelf->quantity())->toBe(50);
    });

    it('flags itself as low below the threshold of 30', function () {
        expect((new Shelf('p-1', 29))->isLow())->toBeTrue()
            ->and((new Shelf('p-1', 30))->isLow())->toBeFalse();
    });
});
