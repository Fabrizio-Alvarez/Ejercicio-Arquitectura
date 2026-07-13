<?php

declare(strict_types=1);

namespace Supermercado\Application\Catalogo;

use Supermercado\Domain\Catalogo\ProductoRepository;

/**
 * Caso de uso: EliminarProducto.
 *
 * Elimina un producto del catálogo. El stock asociado (góndola, depósito)
 * queda huérfano — ListarStock lo muestra con cantidad 0 si el producto
 * ya no existe.
 */
final class EliminarProducto
{
    public function __construct(
        private readonly ProductoRepository $productos,
    ) {}

    public function execute(string $id): void
    {
        $this->productos->delete($id);
    }
}
