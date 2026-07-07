<?php

use Supermarket\Domain\Sales\Sale;
use Supermarket\Domain\Sales\SaleLine;
use Supermarket\Domain\Sales\SaleStatus;
use Supermarket\Domain\Shared\Money;

describe('Sale aggregate', function () {
    it('sums the total of its lines in the same currency', function () {
        $sale = new Sale('s-1', 'c-1', 'Jane Doe');

        $sale->addLine(new SaleLine('p-1', 'Milk', 2, new Money(150, 'ARS')));
        $sale->addLine(new SaleLine('p-2', 'Bread', 1, new Money(300, 'ARS')));

        expect($sale->total())->toEqual(new Money(600, 'ARS')); // 300 + 300
    });

    it('refuses lines in a different currency than the first', function () {
        $sale = new Sale('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new SaleLine('p-1', 'Milk', 1, new Money(150, 'ARS')));

        expect(fn () => $sale->addLine(new SaleLine('p-2', 'Bread', 1, new Money(200, 'USD'))))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('starts as Pending and transitions to Confirmed', function () {
        $sale = new Sale('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new SaleLine('p-1', 'Milk', 1, new Money(150, 'ARS')));

        expect($sale->status())->toBe(SaleStatus::Pending);

        $sale->confirm();

        expect($sale->status())->toBe(SaleStatus::Confirmed);
    });

    it('cannot be confirmed without lines', function () {
        $sale = new Sale('s-1', 'c-1', 'Jane Doe');

        expect(fn () => $sale->confirm())->toThrow(\DomainException::class);
    });

    it('cannot add lines after being confirmed', function () {
        $sale = new Sale('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new SaleLine('p-1', 'Milk', 1, new Money(150, 'ARS')));
        $sale->confirm();

        expect(fn () => $sale->addLine(new SaleLine('p-2', 'Bread', 1, new Money(100, 'ARS'))))
            ->toThrow(\DomainException::class);
    });

    it('cannot be cancelled once confirmed', function () {
        $sale = new Sale('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new SaleLine('p-1', 'Milk', 1, new Money(150, 'ARS')));
        $sale->confirm();

        expect(fn () => $sale->cancel())->toThrow(\DomainException::class);
    });

    it('can be cancelled while pending', function () {
        $sale = new Sale('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new SaleLine('p-1', 'Milk', 1, new Money(150, 'ARS')));
        $sale->cancel();

        expect($sale->status())->toBe(SaleStatus::Cancelled);
    });
});
