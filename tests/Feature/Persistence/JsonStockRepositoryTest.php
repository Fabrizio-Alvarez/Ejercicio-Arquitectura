<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Infrastructure\Persistence\JsonDepositoRepository;
use Supermercado\Infrastructure\Persistence\JsonGondolaRepository;

uses(RefreshDatabase::class); // solo para que TestCase bootee la app; no toca DB.

beforeEach(function () {
    $this->dir = sys_get_temp_dir() . '/sm-json-' . uniqid();
    config(['supermercado.json_dir' => $this->dir]);
});

afterEach(function () {
    foreach (glob($this->dir . '/*') ?: [] as $archivo) {
        unlink($archivo);
    }
    @rmdir($this->dir);
});

describe('JsonGondolaRepository', function () {
    it('persiste y recupera una góndola (save → find → all)', function () {
        $repo = new JsonGondolaRepository();

        $repo->save(new Gondola('p-1', 45));

        $encontrada = $repo->find('p-1');

        expect($encontrada)->not->toBeNull()
            ->and($encontrada->productId())->toBe('p-1')
            ->and($encontrada->quantity())->toBe(45)
            ->and($repo->find('inexistente'))->toBeNull()
            ->and($repo->all())->toHaveCount(1);
    });

    it('hace upsert por product_id: actualiza quantity sin duplicar', function () {
        $repo = new JsonGondolaRepository();

        $repo->save(new Gondola('p-1', 20));
        $repo->save(new Gondola('p-1', 50));

        expect($repo->all())->toHaveCount(1)
            ->and($repo->find('p-1')->quantity())->toBe(50);
    });

    it('mantiene filas independientes por product_id', function () {
        $repo = new JsonGondolaRepository();

        $repo->save(new Gondola('p-1', 10));
        $repo->save(new Gondola('p-2', 25));

        expect($repo->all())->toHaveCount(2)
            ->and($repo->find('p-1')->quantity())->toBe(10)
            ->and($repo->find('p-2')->quantity())->toBe(25);
    });
});

describe('JsonDepositoRepository', function () {
    it('persiste y recupera un depósito (save → find → all)', function () {
        $repo = new JsonDepositoRepository();

        $repo->save(new Deposito('p-1', 200));

        $encontrado = $repo->find('p-1');

        expect($encontrado)->not->toBeNull()
            ->and($encontrado->productId())->toBe('p-1')
            ->and($encontrado->quantity())->toBe(200)
            ->and($repo->find('inexistente'))->toBeNull()
            ->and($repo->all())->toHaveCount(1);
    });

    it('hace upsert por product_id: actualiza quantity sin duplicar', function () {
        $repo = new JsonDepositoRepository();

        $repo->save(new Deposito('p-1', 200));
        $repo->save(new Deposito('p-1', 150));

        expect($repo->all())->toHaveCount(1)
            ->and($repo->find('p-1')->quantity())->toBe(150);
    });

    it('mantiene filas independientes por product_id', function () {
        $repo = new JsonDepositoRepository();

        $repo->save(new Deposito('p-1', 100));
        $repo->save(new Deposito('p-2', 300));

        expect($repo->all())->toHaveCount(2)
            ->and($repo->find('p-1')->quantity())->toBe(100)
            ->and($repo->find('p-2')->quantity())->toBe(300);
    });
});

it('aisla los archivos por repositorio (gondolas.json vs depositos.json)', function () {
    $gondolas = new JsonGondolaRepository();
    $depositos = new JsonDepositoRepository();

    $gondolas->save(new Gondola('p-1', 5));
    $depositos->save(new Deposito('p-1', 500));

    expect($gondolas->all())->toHaveCount(1)
        ->and($depositos->all())->toHaveCount(1)
        ->and(file_exists($this->dir . '/gondolas.json'))->toBeTrue()
        ->and(file_exists($this->dir . '/depositos.json'))->toBeTrue();
});
