<?php

use Supermercado\Domain\Stock\Deposito;

describe('Deposito (almacén)', function () {
    it('decrements quantity when taking stock', function () {
        $warehouse = new Deposito('p-1', 200);
        $warehouse->take(50);

        expect($warehouse->quantity())->toBe(150);
    });

    it('refuses to take more stock than it holds', function () {
        $warehouse = new Deposito('p-1', 10);

        expect(fn () => $warehouse->take(50))->toThrow(DomainException::class);
    });

    it('flags itself as low below the threshold of 150', function () {
        expect((new Deposito('p-1', 149))->isLow())->toBeTrue()
            ->and((new Deposito('p-1', 150))->isLow())->toBeFalse();
    });
    it('caps the amount it can supply at the available stock', function () {
        expect((new Deposito('p-1', 500))->maxAvailableFor(30))->toBe(30) // enough
            ->and((new Deposito('p-1', 10))->maxAvailableFor(30))->toBe(10) // capped
            ->and((new Deposito('p-1', 10))->maxAvailableFor(0))->toBe(0);
    });

    it('projects whether it would be low after extracting stock, without mutating', function () {
        $warehouse = new Deposito('p-1', 160);

        expect($warehouse->wouldBeLowAfter(30))->toBeTrue()    // 160 - 30 = 130 < 150
            ->and($warehouse->quantity())->toBe(160)           // unchanged: it was a projection
            ->and((new Deposito('p-1', 180))->wouldBeLowAfter(30))->toBeFalse(); // 150, not < 150
    });

});
