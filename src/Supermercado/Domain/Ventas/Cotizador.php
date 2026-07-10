<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

use Supermercado\Domain\Catalogo\Ofertas;
use Supermercado\Domain\Catalogo\Producto;

/**
 * Domain service: cotiza un producto para una venta aplicando la mejor oferta
 * activa que lo cubre (o sin descuento si ninguna aplica).
 *
 * Es un servicio de dominio —no una entidad— porque la regla cruza dos
 * agregados: Catálogo (Producto + Ofertas) y Ventas (LineaDeVenta). La selección
 * de la mejor oferta activa vive en la colección Ofertas; este servicio sólo
 * orquesta "cotizar producto -> línea de venta".
 */
final class Cotizador
{
    public function price(Producto $product, int $quantity, Ofertas $offers, \DateTimeImmutable $at): LineaDeVenta
    {
        $best = $offers->bestActiveFor($product->id(), $at);

        $unitPrice = $best !== null
            ? $best->applyTo($product->price())
            : $product->price();

        return new LineaDeVenta($product->id(), $product->name(), $quantity, $unitPrice);
    }
}
