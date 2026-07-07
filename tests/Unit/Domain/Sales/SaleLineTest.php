<?php

use Supermarket\Domain\Sales\SaleLine;
use Supermarket\Domain\Shared\Money;

describe('SaleLine', function () {
    it('computes its total as unit price times quantity', function () {
        $line = new SaleLine('p-1', 'Milk', 3, new Money(150, 'ARS'));

        expect($line->total())->toEqual(new Money(450, 'ARS'))
            ->and($line->currency())->toBe('ARS');
    });

    it('rejects a quantity below 1', function () {
        expect(fn () => new SaleLine('p-1', 'Milk', 0, new Money(150, 'ARS')))
            ->toThrow(\InvalidArgumentException::class);
    });
});
