<?php

namespace App\Listeners;

use Supermercado\Application\Stock\RegistrarReposicion;
use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Stock\UbicacionDeStock;

/**
 * El Repositor: un servicio sin identidad que reacciona a las alertas de stock
 * bajo en la góndola, reponiendo desde el depósito. Las alertas del depósito
 * (backstock bajo) requieren reabastecimiento externo y no se auto-reponen.
 */
final class Repositor
{
    public function __construct(private readonly RegistrarReposicion $reponer) {}

    public function handle(AlertaDeStock $event): void
    {
        if ($event->ubicacion() !== UbicacionDeStock::Gondola) {
            return;
        }

        $this->reponer->execute($event->productId());
    }
}
