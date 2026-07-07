<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermarket\Domain\Sales\Sale;
use Supermarket\Domain\Sales\SaleLine;
use Supermarket\Domain\Sales\SaleRepository;
use Supermarket\Domain\Sales\SaleStatus;
use Supermarket\Domain\Shared\Money;

uses(RefreshDatabase::class);

it('persists and retrieves a confirmed sale with its lines and total', function () {
    $repo = app(SaleRepository::class);

    $sale = new Sale('s-1', 'cashier-1', 'Jane Doe', new \DateTimeImmutable('2026-01-15 10:00:00'));
    $sale->addLine(new SaleLine('p-1', 'Milk', 2, new Money(150, 'ARS')));
    $sale->addLine(new SaleLine('p-2', 'Bread', 1, new Money(300, 'ARS')));
    $sale->confirm();

    $repo->save($sale);

    $found = $repo->find('s-1');

    expect($found)->not->toBeNull()
        ->and($found->status())->toBe(SaleStatus::Confirmed)
        ->and($found->customerName())->toBe('Jane Doe')
        ->and($found->cashierId())->toBe('cashier-1')
        ->and($found->lines())->toHaveCount(2)
        ->and($found->total())->toEqual(new Money(600, 'ARS'));
});

it('returns null for a missing sale', function () {
    expect(app(SaleRepository::class)->find('nope'))->toBeNull();
});

it('persists a pending sale and keeps its status', function () {
    $repo = app(SaleRepository::class);

    $sale = new Sale('s-2', 'cashier-1', 'John', new \DateTimeImmutable('2026-01-15'));
    $sale->addLine(new SaleLine('p-1', 'Milk', 1, new Money(150, 'ARS')));

    $repo->save($sale);

    $found = $repo->find('s-2');

    expect($found->status())->toBe(SaleStatus::Pending)
        ->and($found->lines())->toHaveCount(1);
});

it('lists all persisted sales', function () {
    $repo = app(SaleRepository::class);

    foreach (['s-1', 's-2'] as $id) {
        $sale = new Sale($id, 'cashier-1', 'Jane', new \DateTimeImmutable('2026-01-15'));
        $sale->addLine(new SaleLine('p-1', 'Milk', 1, new Money(150, 'ARS')));
        $sale->confirm();
        $repo->save($sale);
    }

    expect($repo->all())->toHaveCount(2);
});
