<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

/**
 * Evento de dominio: se dispara cuando una venta se confirma.
 *
 * Es un DTO puro (sin dependencias de framework). El agregado Venta lo registra
 * al confirmarse; la capa de aplicación lo despacha para que reaccionen los
 * interesados (descontar stock de la góndola, avisar al depósito, ...).
 */
final class CompraRealizada
{
    /**
     * @param LineaDeVenta[] $lineas
     */
    public function __construct(
        public readonly string $ventaId,
        public readonly MetodoDePago $metodoDePago,
        public readonly array $lineas,
    ) {}
}
