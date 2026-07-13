<?php

declare(strict_types=1);

namespace Supermercado\Application\Catalogo;

use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Dinero;

/**
 * Caso de uso: CrearProducto.
 *
 * El depositista da de alta un producto nuevo en el catálogo con su precio
 * inicial. Falla si ya existe un producto con el mismo id.
 */
final class CrearProducto
{
    public function __construct(
        private readonly ProductoRepository $productos,
    ) {}

    public function execute(string $id, string $nombre, int $precioCentavos, string $moneda): Producto
    {
        if ($this->productos->find($id) !== null) {
            throw new \DomainException("Ya existe un producto con el id {$id}.");
        }

        $producto = new Producto($id, $nombre, new Dinero($precioCentavos, $moneda));
        $this->productos->save($producto);

        return $producto;
    }
}
