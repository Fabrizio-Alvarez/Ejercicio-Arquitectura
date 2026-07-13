<?php

declare(strict_types=1);

namespace Supermercado\Application\Tableros;

use Supermercado\Application\Stock\ListarStock;
use Supermercado\Application\Stock\VistaDeStock;

/**
 * Caso de uso: ObtenerTableroRepositor.
 *
 * Consolida los KPIs de stock para el tablero del repositor: productos
 * con góndola baja, depósito bajo y el listado filtrado de stock crítico.
 */
final class ObtenerTableroRepositor
{
    public function __construct(
        private readonly ListarStock $listarStock,
    ) {}

    public function execute(): TableroRepositorView
    {
        $stock = $this->listarStock->execute();

        $gondolaBaja = array_filter($stock, static fn (VistaDeStock $v): bool => $v->shelfLow);
        $depositoBajo = array_filter($stock, static fn (VistaDeStock $v): bool => $v->warehouseLow);
        $critico = array_filter(
            $stock,
            static fn (VistaDeStock $v): bool => $v->shelfLow || $v->warehouseLow,
        );

        return new TableroRepositorView(
            productosGondolaBaja: count($gondolaBaja),
            productosDepositoBajo: count($depositoBajo),
            totalProductos: count($stock),
            stockCritico: array_values($critico),
        );
    }
}
