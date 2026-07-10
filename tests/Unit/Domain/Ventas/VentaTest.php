<?php

use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Ventas\EstadoDeVenta;
use Supermercado\Domain\Ventas\LineaDeVenta;
use Supermercado\Domain\Ventas\Venta;

describe('Venta aggregate', function () {
    it('sums the total of its lines in the same currency', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');

        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 2, new Dinero(150, 'ARS')));
        $sale->addLine(new LineaDeVenta('p-2', 'Bread', 1, new Dinero(300, 'ARS')));

        expect($sale->total())->toEqual(new Dinero(600, 'ARS')); // 300 + 300
    });

    it('refuses lines in a different currency than the first', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));

        expect(fn () => $sale->addLine(new LineaDeVenta('p-2', 'Bread', 1, new Dinero(200, 'USD'))))
            ->toThrow(InvalidArgumentException::class);
    });

    it('starts as Pendiente and transitions to Confirmada', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));

        expect($sale->status())->toBe(EstadoDeVenta::Pendiente);

        $sale->confirm();

        expect($sale->status())->toBe(EstadoDeVenta::Confirmada);
    });

    it('cannot be confirmed without lines', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');

        expect(fn () => $sale->confirm())->toThrow(DomainException::class);
    });

    it('cannot add lines after being confirmed', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
        $sale->confirm();

        expect(fn () => $sale->addLine(new LineaDeVenta('p-2', 'Bread', 1, new Dinero(100, 'ARS'))))
            ->toThrow(DomainException::class);
    });

    it('cannot be cancelled once confirmed', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
        $sale->confirm();

        expect(fn () => $sale->cancel())->toThrow(DomainException::class);
    });

    it('can be cancelled while pending', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
        $sale->cancel();

        expect($sale->status())->toBe(EstadoDeVenta::Cancelada);
    });

    it('exposes state predicates and belongs-to / on-day rules', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane', new DateTimeImmutable('2026-01-15 10:00:00'));
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));

        expect($sale->isPending())->toBeTrue()
            ->and($sale->isConfirmed())->toBeFalse()
            ->and($sale->isForCashier('c-1'))->toBeTrue()
            ->and($sale->isForCashier('c-2'))->toBeFalse()
            ->and($sale->isOnDay(new DateTimeImmutable('2026-01-15')))->toBeTrue()
            ->and($sale->isOnDay(new DateTimeImmutable('2026-01-16')))->toBeFalse();

        $sale->confirm();

        expect($sale->isConfirmed())->toBeTrue()
            ->and($sale->isPending())->toBeFalse();
    });

    it('counts distinct lines and total units sold', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 2, new Dinero(150, 'ARS')));
        $sale->addLine(new LineaDeVenta('p-2', 'Bread', 3, new Dinero(100, 'ARS')));

        expect($sale->lineCount())->toBe(2)
            ->and($sale->itemCount())->toBe(5); // 2 + 3
    });
});
