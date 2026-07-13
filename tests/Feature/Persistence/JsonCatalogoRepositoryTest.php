<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Domain\Catalogo\Oferta;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Infrastructure\Persistence\JsonOfertaRepository;
use Supermercado\Infrastructure\Persistence\JsonProductoRepository;

uses(RefreshDatabase::class); // solo para que TestCase bootee la app; no toca DB

beforeEach(function () {
    $this->dir = sys_get_temp_dir() . '/sm-json-' . uniqid();
    @mkdir($this->dir, 0777, true);
    config(['supermercado.json_dir' => $this->dir]);
});

afterEach(function () {
    foreach ((array) glob($this->dir . '/*') as $archivo) {
        @unlink($archivo);
    }
    @rmdir($this->dir);
});

it('persiste y recupera un producto con su Dinero intacto', function () {
    $repo = new JsonProductoRepository();

    $repo->save(new Producto('p-1', 'Café 1kg', new Dinero(1099, 'ARS')));

    $encontrado = $repo->find('p-1');
    expect($encontrado)->not->toBeNull()
        ->and($encontrado->id())->toBe('p-1')
        ->and($encontrado->name())->toBe('Café 1kg')
        ->and($encontrado->price()->amount())->toBe(1099)
        ->and($encontrado->price()->currency())->toBe('ARS');

    $todos = $repo->all();
    expect($todos)->toHaveCount(1)
        ->and($todos[0]->price())->toEqual(new Dinero(1099, 'ARS'));
});

it('hace upsert por id al guardar un producto existente', function () {
    $repo = new JsonProductoRepository();

    $repo->save(new Producto('p-1', 'Café 1kg', new Dinero(1099, 'ARS')));
    $repo->save(new Producto('p-1', 'Café 1kg Premium', new Dinero(1499, 'ARS')));

    expect($repo->all())->toHaveCount(1)
        ->and($repo->find('p-1')->name())->toBe('Café 1kg Premium')
        ->and($repo->find('p-1')->price()->amount())->toBe(1499);
});

it('devuelve null al buscar un producto inexistente', function () {
    expect((new JsonProductoRepository())->find('no-existe'))->toBeNull();
});

it('lee ofertas desde JSON en disco respetando percent y ventana de validez', function () {
    $repo = new JsonOfertaRepository();

    file_put_contents($repo->rutaDeArchivo(), json_encode([
        [
            'product_id' => 'p-1',
            'percent' => 25.0,
            'valid_from' => '2026-01-01T00:00:00+00:00',
            'valid_to' => '2026-01-31T23:59:59+00:00',
        ],
        [
            'product_id' => 'p-2',
            'percent' => 10.0,
            'valid_from' => '2026-02-01T00:00:00+00:00',
            'valid_to' => '2026-02-28T23:59:59+00:00',
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $ofertasP1 = $repo->findByProduct('p-1');
    expect($ofertasP1)->toHaveCount(1)
        ->and($ofertasP1[0])->toBeInstanceOf(Oferta::class)
        ->and($ofertasP1[0]->productId())->toBe('p-1')
        ->and($ofertasP1[0]->percent())->toBe(25.0)
        ->and($ofertasP1[0]->isActive(new \DateTimeImmutable('2026-01-15T12:00:00+00:00')))->toBeTrue()
        ->and($ofertasP1[0]->isActive(new \DateTimeImmutable('2026-02-15T12:00:00+00:00')))->toBeFalse();

    expect($repo->all())->toHaveCount(2);
});

it('devuelve una lista vacía para un producto sin ofertas', function () {
    $repo = new JsonOfertaRepository();

    file_put_contents($repo->rutaDeArchivo(), json_encode([
        ['product_id' => 'p-1', 'percent' => 25.0, 'valid_from' => '2026-01-01T00:00:00+00:00', 'valid_to' => '2026-01-31T23:59:59+00:00'],
    ]));

    expect($repo->findByProduct('sin-ofertas'))->toBeEmpty();
});
