<?php

declare(strict_types=1);

namespace Supermercado\Application\Ventas;

final class ProductoNoEncontradoException extends \DomainException
{
    public static function forId(string $productId): self
    {
        return new self("Producto not found: {$productId}.");
    }
}
