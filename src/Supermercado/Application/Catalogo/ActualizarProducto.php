<?php

declare(strict_types=1);

namespace Supermercado\Application\Catalogo;

use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Dinero;

/**
 * Caso de uso: ActualizarProducto.
 *
 * Modifica el nombre y/o precio de un producto existente. Usa los métodos
 * de mutación del agregado (rename / changePrice) y lo persiste.
 */
final class ActualizarProducto
{
    public function __construct(
        private readonly ProductoRepository $productos,
    ) {}

    public function execute(string $id, string $nombre, int $precioCentavos, string $moneda): void
    {
        $producto = $this->productos->find($id)
            ?? throw new \DomainException("No existe el producto {$id}.");

        $producto->rename($nombre);
        $producto->changePrice(new Dinero($precioCentavos, $moneda));

        $this->productos->save($producto);
    }
}
