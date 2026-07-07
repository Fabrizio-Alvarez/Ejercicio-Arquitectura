<?php

use Supermarket\Domain\Stock\Warehouse;

describe('Warehouse (almacén)', function () {
    it('decrements quantity when taking stock', function () {
        $warehouse = new Warehouse('p-1', 200);
        $warehouse->take(50);

        expect($warehouse->quantity())->toBe(150);
    });

    it('refuses to take more stock than it holds', function () {
        $warehouse = new Warehouse('p-1', 10);

        expect(fn () => $warehouse->take(50))->toThrow(\DomainException::class);
    });

    it('flags itself as low below the threshold of 150', function () {
        expect((new Warehouse('p-1', 149))->isLow())->toBeTrue()
            ->and((new Warehouse('p-1', 150))->isLow())->toBeFalse();
    });
});
