<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

use Supermercado\Domain\Catalogo\Ofertas;
use Supermercado\Domain\Catalogo\Producto;

/**
 * Un item del carrito: un producto resuelto, la cantidad deseada y las ofertas
 * activas que lo cubren.
 *
 * A diferencia de ItemRequest (DTO de aplicación con strings), este value object
 * vive en el dominio y ya tiene el Producto y las Ofertas resueltas — no necesita
 * repositorios. Es lo que el Carrito usa para cotizar.
 */
final class ItemCarrito
{
    public function __construct(
        public readonly Producto $producto,
        public readonly int $cantidad,
        public readonly Ofertas $ofertas,
    ) {
        if ($cantidad < 1) {
            throw new \InvalidArgumentException("La cantidad debe ser >= 1, se recibió {$cantidad}.");
        }
    }
}
