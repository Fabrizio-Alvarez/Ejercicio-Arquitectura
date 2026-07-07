<?php

use Supermarket\Domain\Sales\CashClose;
use Supermarket\Domain\Sales\Sale;
use Supermarket\Domain\Sales\SaleLine;
use Supermarket\Domain\Shared\Money;

function saleFor(string $id, string $cashier, string $customer, string $date, int $amount, bool $confirm = true): Sale
{
    $sale = new Sale($id, $cashier, $customer, new \DateTimeImmutable($date));
    $sale->addLine(new SaleLine('p-1', 'Item', 1, new Money($amount, 'ARS')));

    if ($confirm) {
        $sale->confirm();
    }

    return $sale;
}

describe('Cash close (cierre de caja)', function () {
    it('keeps only confirmed sales of the cashier on the given day', function () {
        $sales = [
            saleFor('s-1', 'c-1', 'Jane', '2026-01-15 10:00:00', 150),
            saleFor('s-2', 'c-1', 'John', '2026-01-15 14:00:00', 300),
            saleFor('s-3', 'c-1', 'Jill', '2026-01-16 09:00:00', 200),   // wrong day
            saleFor('s-4', 'c-2', 'Jack', '2026-01-15 11:00:00', 400),   // wrong cashier
        ];

        $close = CashClose::forCashierOn('c-1', new \DateTimeImmutable('2026-01-15'), ...$sales);

        expect($close->count())->toBe(2)
            ->and($close->total())->toEqual(new Money(450, 'ARS')) // 150 + 300
            ->and($close->rows()[0]->customerName())->toBe('Jane')
            ->and($close->rows()[1]->customerName())->toBe('John');
    });

    it('ignores sales that are not confirmed (pending or cancelled)', function () {
        $pending = saleFor('s-1', 'c-1', 'Jane', '2026-01-15 10:00:00', 150, confirm: false);
        $cancelled = saleFor('s-2', 'c-1', 'John', '2026-01-15 11:00:00', 300, confirm: false);
        $cancelled->cancel();
        $confirmed = saleFor('s-3', 'c-1', 'Jill', '2026-01-15 12:00:00', 200);

        $close = CashClose::forCashierOn('c-1', new \DateTimeImmutable('2026-01-15'), $pending, $cancelled, $confirmed);

        expect($close->count())->toBe(1)
            ->and($close->total())->toEqual(new Money(200, 'ARS'));
    });

    it('produces an empty close (no rows) when nothing matches', function () {
        $close = CashClose::forCashierOn('c-1', new \DateTimeImmutable('2026-01-15'));

        expect($close->count())->toBe(0);
    });

    it('refuses to total an empty close', function () {
        $close = CashClose::forCashierOn('c-1', new \DateTimeImmutable('2026-01-15'));

        expect(fn () => $close->total())->toThrow(\DomainException::class);
    });
});
