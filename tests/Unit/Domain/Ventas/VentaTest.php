<?php

use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Ventas\EstadoDeVenta;
use Supermercado\Domain\Ventas\LineaDeVenta;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\ItemDevolucion;
use Supermercado\Domain\Ventas\DevolucionRegistrada;

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

    it('transitions Pendiente → EsperandoPago → Confirmada', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));

        expect($sale->status())->toBe(EstadoDeVenta::Pendiente);

        $sale->marcarEsperandoPago();

        expect($sale->status())->toBe(EstadoDeVenta::EsperandoPago)
            ->and($sale->isEsperandoPago())->toBeTrue();

        $sale->confirm();

        expect($sale->status())->toBe(EstadoDeVenta::Confirmada);
    });

    it('cannot be confirmed without lines', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->marcarEsperandoPago();

        expect(fn () => $sale->confirm())->toThrow(DomainException::class);
    });

    it('cannot confirm from Pendiente (must go through EsperandoPago)', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));

        expect(fn () => $sale->confirm())->toThrow(DomainException::class);
    });

    it('cannot add lines after being confirmed', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
        $sale->marcarEsperandoPago();
        $sale->confirm();

        expect(fn () => $sale->addLine(new LineaDeVenta('p-2', 'Bread', 1, new Dinero(100, 'ARS'))))
            ->toThrow(DomainException::class);
    });

    it('cannot add lines after being marked EsperandoPago', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
        $sale->marcarEsperandoPago();

        expect(fn () => $sale->addLine(new LineaDeVenta('p-2', 'Bread', 1, new Dinero(100, 'ARS'))))
            ->toThrow(DomainException::class);
    });

    it('cannot be cancelled once confirmed', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
        $sale->marcarEsperandoPago();
        $sale->confirm();

        expect(fn () => $sale->cancel())->toThrow(DomainException::class);
    });

    it('can be cancelled while esperando pago', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
        $sale->marcarEsperandoPago();
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

        $sale->marcarEsperandoPago();
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

    it('registers a devolucion recording a DevolucionRegistrada event', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 5, new Dinero(150, 'ARS')));
        $sale->marcarEsperandoPago();
        $sale->confirm();

        $sale->registrarDevolucion([new ItemDevolucion('p-1', 2)]);

        $eventos = $sale->eventos();
        expect($eventos)->toHaveCount(2); // CompraRealizada + DevolucionRegistrada
        $devolucion = $eventos[1];
        expect($devolucion)->toBeInstanceOf(DevolucionRegistrada::class)
            ->and($devolucion->ventaId)->toBe('s-1')
            ->and($devolucion->items)->toHaveCount(1)
            ->and($devolucion->items[0]->productoId())->toBe('p-1')
            ->and($devolucion->items[0]->cantidad())->toBe(2);
    });

    it('refuses devolucion on a non-confirmed sale', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 1, new Dinero(150, 'ARS')));
        $sale->marcarEsperandoPago();
        // Not confirmed yet.

        expect(fn () => $sale->registrarDevolucion([new ItemDevolucion('p-1', 1)]))
            ->toThrow(\DomainException::class);
    });

    it('refuses returning more than was sold', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 3, new Dinero(150, 'ARS')));
        $sale->marcarEsperandoPago();
        $sale->confirm();

        expect(fn () => $sale->registrarDevolucion([new ItemDevolucion('p-1', 4)]))
            ->toThrow(\DomainException::class);
    });

    it('refuses returning a product not in the sale', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 3, new Dinero(150, 'ARS')));
        $sale->marcarEsperandoPago();
        $sale->confirm();

        expect(fn () => $sale->registrarDevolucion([new ItemDevolucion('p-99', 1)]))
            ->toThrow(\DomainException::class);
    });

    it('refuses an empty devolucion', function () {
        $sale = new Venta('s-1', 'c-1', 'Jane Doe');
        $sale->addLine(new LineaDeVenta('p-1', 'Milk', 3, new Dinero(150, 'ARS')));
        $sale->marcarEsperandoPago();
        $sale->confirm();

        expect(fn () => $sale->registrarDevolucion([]))
            ->toThrow(\DomainException::class);
    });
});
