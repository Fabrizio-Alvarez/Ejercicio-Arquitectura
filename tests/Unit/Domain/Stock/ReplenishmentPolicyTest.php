<?php

use Supermarket\Domain\Stock\ReplenishmentPolicy;
use Supermarket\Domain\Stock\Shelf;
use Supermarket\Domain\Stock\Warehouse;

describe('Replenishment policy', function () {
    it('does nothing when the shelf is not low (>=30)', function () {
        $policy = new ReplenishmentPolicy();
        $result = $policy->decide(new Shelf('p-1', 40), new Warehouse('p-1', 500));

        expect($result->hasReplenishment())->toBeFalse()
            ->and($result->quantityToMove())->toBe(0)
            ->and($result->emitsAlert())->toBeFalse();
    });

    it('replenishes a low shelf up to 50 when the warehouse has enough', function () {
        $policy = new ReplenishmentPolicy();
        // shelf 20 -> needs 30 to reach 50; warehouse 500 -> projected 470 (no alert)
        $result = $policy->decide(new Shelf('p-1', 20), new Warehouse('p-1', 500));

        expect($result->quantityToMove())->toBe(30)
            ->and($result->emitsAlert())->toBeFalse();
    });

    it('emits an alert when the projected warehouse drops below 150', function () {
        $policy = new ReplenishmentPolicy();
        // shelf 20 -> needs 30; warehouse 160 -> projected 130 (< 150 -> alert)
        $result = $policy->decide(new Shelf('p-1', 20), new Warehouse('p-1', 160));

        expect($result->quantityToMove())->toBe(30)
            ->and($result->emitsAlert())->toBeTrue();
    });

    it('caps the move at the available warehouse stock and still alerts when low', function () {
        $policy = new ReplenishmentPolicy();
        // shelf 20 -> needs 30; warehouse 10 -> can only move 10 -> projected 0 (< 150 -> alert)
        $result = $policy->decide(new Shelf('p-1', 20), new Warehouse('p-1', 10));

        expect($result->quantityToMove())->toBe(10)
            ->and($result->emitsAlert())->toBeTrue();
    });

    it('does not alert at the exact 150 boundary after replenishing', function () {
        $policy = new ReplenishmentPolicy();
        // shelf 20 -> needs 30; warehouse 180 -> projected exactly 150 (NOT < 150 -> no alert)
        $result = $policy->decide(new Shelf('p-1', 20), new Warehouse('p-1', 180));

        expect($result->quantityToMove())->toBe(30)
            ->and($result->emitsAlert())->toBeFalse();
    });
});
