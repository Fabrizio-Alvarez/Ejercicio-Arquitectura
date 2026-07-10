<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Domain\Catalogo\OfertaRepository;
use Supermercado\Infrastructure\Persistence\OfertaModel;

uses(RefreshDatabase::class);

it('loads offers for a product as domain value objects', function () {
    OfertaModel::create([
        'product_id' => 'p-1',
        'percent' => 25.00,
        'valid_from' => '2026-01-01 00:00:00',
        'valid_to' => '2026-01-31 23:59:59',
    ]);

    $offers = app(OfertaRepository::class)->findByProduct('p-1');

    expect($offers)->toHaveCount(1)
        ->and($offers[0]->percent())->toBe(25.0)
        ->and($offers[0]->isActive(new \DateTimeImmutable('2026-01-15 12:00:00')))->toBeTrue();
});

it('returns an empty list for a product with no offers', function () {
    expect(app(OfertaRepository::class)->findByProduct('nope'))->toBeEmpty();
});
