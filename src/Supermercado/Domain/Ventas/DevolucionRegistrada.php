<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

/**
 * Evento de dominio: se dispara cuando se registra una devolución sobre una
 * venta confirmada. Lleva los items devueltos para que los interesados
 * restauren stock en la góndola y dejen huella en el depósito.
 */
final class DevolucionRegistrada
{
    /**
     * @param ItemDevolucion[] $items
     */
    public function __construct(
        public readonly string $ventaId,
        public readonly array $items,
    ) {}
}
