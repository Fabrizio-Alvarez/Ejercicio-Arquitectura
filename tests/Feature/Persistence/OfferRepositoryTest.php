<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermarket\Domain\Catalog\OfferRepository;
use Supermarket\Infrastructure\Persistence\OfferModel;

uses(RefreshDatabase::class);

it('loads offers for a product as domain value objects', function () {
    OfferModel::create([
        'product_id' => 'p-1',
        'percent' => 25.00,
        'valid_from' => '2026-01-01 00:00:00',
        'valid_to' => '2026-01-31 23:59:59',
    ]);

    $offers = app(OfferRepository::class)->findByProduct('p-1');

    expect($offers)->toHaveCount(1)
        ->and($offers[0]->percent())->toBe(25.0)
        ->and($offers[0]->isActive(new \DateTimeImmutable('2026-01-15 12:00:00')))->toBeTrue();
});

it('returns an empty list for a product with no offers', function () {
    expect(app(OfferRepository::class)->findByProduct('nope'))->toBeEmpty();
});
