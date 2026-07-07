<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermarket\Application\Sales\ObtenerCierreDeCaja;
use Supermarket\Domain\Sales\Sale;
use Supermarket\Domain\Sales\SaleLine;
use Supermarket\Domain\Sales\SaleRepository;
use Supermarket\Domain\Shared\Money;

uses(RefreshDatabase::class);

function confirmSale(string $id, string $cashier, string $customer, string $date, int $amount): Sale
{
    $sale = new Sale($id, $cashier, $customer, new \DateTimeImmutable($date));
    $sale->addLine(new SaleLine('p-1', 'Item', 1, new Money($amount, 'ARS')));
    $sale->confirm();
    app(SaleRepository::class)->save($sale);

    return $sale;
}

it('builds the cash close for a cashier on a given day', function () {
    confirmSale('s-1', 'cashier-1', 'Jane', '2026-01-15 10:00:00', 150);
    confirmSale('s-2', 'cashier-1', 'John', '2026-01-15 14:00:00', 300);
    confirmSale('s-3', 'cashier-2', 'Jill', '2026-01-15 11:00:00', 400); // other cashier
    confirmSale('s-4', 'cashier-1', 'Jack', '2026-01-16 09:00:00', 200); // other day

    $close = app(ObtenerCierreDeCaja::class)->execute('cashier-1', new \DateTimeImmutable('2026-01-15'));

    expect($close->count())->toBe(2)
        ->and($close->total())->toEqual(new Money(450, 'ARS'));
});
