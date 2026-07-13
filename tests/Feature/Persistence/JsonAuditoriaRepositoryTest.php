<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Stock\MovimientoDeStock;
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;
use Supermercado\Infrastructure\Persistence\JsonAlertaDeStockRepository;
use Supermercado\Infrastructure\Persistence\JsonMovimientoDeStockRepository;

uses(RefreshDatabase::class);

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

describe('JsonMovimientoDeStockRepository', function () {
    it('persiste (append) y recupera múltiples movimientos ordenados por fecha DESC', function () {
        $repo = new JsonMovimientoDeStockRepository();

        $viejo = new MovimientoDeStock(
            'mov-viejo',
            'prod-1',
            TipoDeMovimiento::Reposicion,
            50,
            UbicacionDeStock::Deposito,
            new DateTimeImmutable('2026-01-01T10:00:00+00:00'),
        );
        $nuevo = new MovimientoDeStock(
            'mov-nuevo',
            'prod-1',
            TipoDeMovimiento::Venta,
            5,
            UbicacionDeStock::Gondola,
            new DateTimeImmutable('2026-07-01T10:00:00+00:00'),
            'venta-42',
        );

        $repo->save($viejo);
        $repo->save($nuevo);

        $todos = $repo->all();

        // save es APPEND: dos saves => dos filas
        expect($todos)->toHaveCount(2);
        // ordenadas por fecha DESC (el más reciente primero). El id se regenera en
        // save, así que distinguimos por cantidad/tipo del movimiento.
        expect($todos[0]->cantidad())->toBe(5)
            ->and($todos[0]->tipo())->toBe(TipoDeMovimiento::Venta)
            ->and($todos[1]->cantidad())->toBe(50)
            ->and($todos[1]->tipo())->toBe(TipoDeMovimiento::Reposicion);
    });

    it('hidrata correctamente todos los campos del movimiento', function () {
        $repo = new JsonMovimientoDeStockRepository();

        $mov = new MovimientoDeStock(
            'mov-1',
            'prod-99',
            TipoDeMovimiento::Ajuste,
            7,
            UbicacionDeStock::Deposito,
            new DateTimeImmutable('2026-03-15T08:30:00+00:00'),
            'ajuste-inventario',
        );
        $repo->save($mov);

        $recuperado = $repo->all()[0];

        expect($recuperado->productoId())->toBe('prod-99')
            ->and($recuperado->tipo())->toBe(TipoDeMovimiento::Ajuste)
            ->and($recuperado->cantidad())->toBe(7)
            ->and($recuperado->ubicacion())->toBe(UbicacionDeStock::Deposito)
            ->and($recuperado->fecha()->format(DateTimeInterface::ATOM))->toBe('2026-03-15T08:30:00+00:00')
            ->and($recuperado->referencia())->toBe('ajuste-inventario');
    });

    it('filtra movimientos por producto ordenados por fecha DESC', function () {
        $repo = new JsonMovimientoDeStockRepository();

        $repo->save(new MovimientoDeStock('m-a', 'prod-1', TipoDeMovimiento::Venta, 1, UbicacionDeStock::Gondola, new DateTimeImmutable('2026-01-01T10:00:00+00:00')));
        $repo->save(new MovimientoDeStock('m-b', 'prod-2', TipoDeMovimiento::Venta, 1, UbicacionDeStock::Gondola, new DateTimeImmutable('2026-06-01T10:00:00+00:00')));
        $repo->save(new MovimientoDeStock('m-c', 'prod-1', TipoDeMovimiento::Reposicion, 10, UbicacionDeStock::Deposito, new DateTimeImmutable('2026-05-01T10:00:00+00:00')));

        $deProd1 = $repo->findByProducto('prod-1');

        expect($deProd1)->toHaveCount(2);
        // El id se regenera en save; distinguimos por cantidad (mayo=10, enero=1).
        expect($deProd1[0]->cantidad())->toBe(10);   // mayo (más reciente de prod-1)
        expect($deProd1[1]->cantidad())->toBe(1);   // enero
    });

    it('acepta referencia nula', function () {
        $repo = new JsonMovimientoDeStockRepository();

        $repo->save(new MovimientoDeStock(
            'm-null',
            'prod-1',
            TipoDeMovimiento::Ajuste,
            3,
            UbicacionDeStock::Gondola,
            new DateTimeImmutable('2026-01-01T10:00:00+00:00'),
            null,
        ));

        $recuperado = $repo->all()[0];
        expect($recuperado->referencia())->toBeNull();
    });
});

describe('JsonAlertaDeStockRepository', function () {
    it('persiste (append) y recupera múltiples alertas ordenadas por fecha DESC', function () {
        $repo = new JsonAlertaDeStockRepository();

        $vieja = new AlertaDeStock(
            'prod-1',
            UbicacionDeStock::Deposito,
            120,
            new DateTimeImmutable('2026-01-01T10:00:00+00:00'),
        );
        $nueva = new AlertaDeStock(
            'prod-1',
            UbicacionDeStock::Gondola,
            5,
            new DateTimeImmutable('2026-07-01T10:00:00+00:00'),
        );

        $repo->save($vieja);
        $repo->save($nueva);

        $todas = $repo->all();

        expect($todas)->toHaveCount(2);
        // más reciente primero (id se regenera en save; distinguimos por cantidad).
        expect($todas[0]->cantidad())->toBe(5);
        expect($todas[1]->cantidad())->toBe(120);
    });

    it('hidrata correctamente los campos de la alerta', function () {
        $repo = new JsonAlertaDeStockRepository();

        $repo->save(new AlertaDeStock(
            'prod-7',
            UbicacionDeStock::Gondola,
            3,
            new DateTimeImmutable('2026-04-10T12:00:00+00:00'),
        ));

        $recuperada = $repo->all()[0];

        expect($recuperada->productId())->toBe('prod-7')
            ->and($recuperada->ubicacion())->toBe(UbicacionDeStock::Gondola)
            ->and($recuperada->cantidad())->toBe(3)
            ->and($recuperada->at()->format(DateTimeInterface::ATOM))->toBe('2026-04-10T12:00:00+00:00');
    });

    it('filtra alertas por producto ordenadas por fecha DESC', function () {
        $repo = new JsonAlertaDeStockRepository();

        $repo->save(new AlertaDeStock('prod-1', UbicacionDeStock::Deposito, 100, new DateTimeImmutable('2026-01-01T10:00:00+00:00')));
        $repo->save(new AlertaDeStock('prod-2', UbicacionDeStock::Gondola, 5, new DateTimeImmutable('2026-06-01T10:00:00+00:00')));
        $repo->save(new AlertaDeStock('prod-1', UbicacionDeStock::Gondola, 8, new DateTimeImmutable('2026-05-01T10:00:00+00:00')));

        $deProd1 = $repo->findByProducto('prod-1');

        expect($deProd1)->toHaveCount(2);
        expect($deProd1[0]->cantidad())->toBe(8);   // mayo (más reciente de prod-1)
        expect($deProd1[1]->cantidad())->toBe(100); // enero
    });
});
