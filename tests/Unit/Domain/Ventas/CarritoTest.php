<?php

use Supermercado\Domain\Catalogo\Oferta;
use Supermercado\Domain\Catalogo\Ofertas;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Ventas\Carrito;
use Supermercado\Domain\Ventas\Cotizador;
use Supermercado\Domain\Ventas\EstadoDeVenta;
use Supermercado\Domain\Ventas\ItemCarrito;
use Supermercado\Domain\Ventas\MetodoDePago;

describe('Carrito value object', function () {
    it('ensambla una venta Pendiente con las líneas cotizadas', function () {
        $pricer = new Cotizador;
        $now = new DateTimeImmutable('2026-01-15');

        $producto = new Producto('p-1', 'Leche', new Dinero(1000, 'ARS'));
        $carrito = new Carrito([
            new ItemCarrito($producto, 2, new Ofertas),
        ]);

        $venta = $carrito->cobrar($pricer, $now, 'v-1', 'c-1', 'Cliente', MetodoDePago::Efectivo);

        expect($venta->status())->toBe(EstadoDeVenta::Pendiente)
            ->and($venta->lineCount())->toBe(1)
            ->and($venta->itemCount())->toBe(2)
            ->and($venta->total())->toEqual(new Dinero(2000, 'ARS'));
    });

    it('aplica la mejor oferta activa al cotizar cada item', function () {
        $pricer = new Cotizador;
        $now = new DateTimeImmutable('2026-01-15');

        $producto = new Producto('p-1', 'Leche', new Dinero(1000, 'ARS'));
        $oferta = new Oferta('p-1', 25.0, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-01-31'));

        $carrito = new Carrito([
            new ItemCarrito($producto, 1, new Ofertas([$oferta])),
        ]);

        $venta = $carrito->cobrar($pricer, $now, 'v-1', 'c-1', 'Cliente', MetodoDePago::Efectivo);

        // 1000 - 25% = 750
        expect($venta->total())->toEqual(new Dinero(750, 'ARS'));
    });

    it('maneja múltiples items con y sin oferta', function () {
        $pricer = new Cotizador;
        $now = new DateTimeImmutable('2026-01-15');

        $leche = new Producto('p-1', 'Leche', new Dinero(1000, 'ARS'));
        $pan = new Producto('p-2', 'Pan', new Dinero(500, 'ARS'));
        $ofertaLeche = new Oferta('p-1', 25.0, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-01-31'));

        $carrito = new Carrito([
            new ItemCarrito($leche, 2, new Ofertas([$ofertaLeche])), // 2 x 750 = 1500
            new ItemCarrito($pan, 3, new Ofertas),                    // 3 x 500 = 1500
        ]);

        $venta = $carrito->cobrar($pricer, $now, 'v-1', 'c-1', 'Cliente', MetodoDePago::Efectivo);

        expect($venta->lineCount())->toBe(2)
            ->and($venta->itemCount())->toBe(5)
            ->and($venta->total())->toEqual(new Dinero(3000, 'ARS'));
    });

    it('falla si se cobra un carrito vacío', function () {
        $pricer = new Cotizador;

        $carrito = new Carrito;

        expect(fn () => $carrito->cobrar(
            $pricer, new DateTimeImmutable, 'v-1', 'c-1', 'C', MetodoDePago::Efectivo,
        ))->toThrow(DomainException::class);
    });

    it('agregar devuelve un nuevo carrito sin mutar el original', function () {
        $producto = new Producto('p-1', 'Leche', new Dinero(1000, 'ARS'));
        $item = new ItemCarrito($producto, 1, new Ofertas);

        $carrito = new Carrito;
        $conItem = $carrito->agregar($item);

        expect($carrito->estaVacio())->toBeTrue()
            ->and($conItem->cantidad())->toBe(1)
            ->and($carrito)->not->toBe($conItem);
    });

    it('remover devuelve un nuevo carrito sin el producto indicado', function () {
        $leche = new Producto('p-1', 'Leche', new Dinero(1000, 'ARS'));
        $pan = new Producto('p-2', 'Pan', new Dinero(500, 'ARS'));

        $carrito = new Carrito([
            new ItemCarrito($leche, 1, new Ofertas),
            new ItemCarrito($pan, 2, new Ofertas),
        ]);

        $sinLeche = $carrito->remover('p-1');

        expect($carrito->cantidad())->toBe(2) // original intacto
            ->and($sinLeche->cantidad())->toBe(1)
            ->and($sinLeche->items()[0]->producto->id())->toBe('p-2');
    });

    it('remover un producto inexistente es no-op', function () {
        $producto = new Producto('p-1', 'Leche', new Dinero(1000, 'ARS'));
        $carrito = new Carrito([new ItemCarrito($producto, 1, new Ofertas)]);

        $resultado = $carrito->remover('p-inexistente');

        expect($resultado->cantidad())->toBe(1);
    });

    it('cantidad y estaVacio reportan el estado correcto', function () {
        $carrito = new Carrito;

        expect($carrito->cantidad())->toBe(0)
            ->and($carrito->estaVacio())->toBeTrue();

        $conItem = $carrito->agregar(
            new ItemCarrito(new Producto('p-1', 'Leche', new Dinero(1000, 'ARS')), 1, new Ofertas),
        );

        expect($conItem->cantidad())->toBe(1)
            ->and($conItem->estaVacio())->toBeFalse();
    });
});

describe('ItemCarrito', function () {
    it('rechaza cantidad menor a 1', function () {
        $producto = new Producto('p-1', 'Leche', new Dinero(1000, 'ARS'));

        expect(fn () => new ItemCarrito($producto, 0, new Ofertas))
            ->toThrow(InvalidArgumentException::class);
    });
});
