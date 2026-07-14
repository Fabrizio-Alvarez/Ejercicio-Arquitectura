<?php

namespace App\Listeners;

use Illuminate\Support\Str;
use Supermercado\Domain\Comun\Clock;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\MovimientoDeStock;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;
use Supermercado\Domain\Ventas\DevolucionRegistrada;

/**
 * Reacciona a DevolucionRegistrada: restaura el stock en la góndola
 * (restock) y deja huella del movimiento en el depósito.
 */
final class RestaurarStockPorDevolucion
{
    public function __construct(
        private readonly GondolaRepository $gondolas,
        private readonly MovimientoDeStockRepository $movimientos,
        private readonly Clock $clock,
    ) {}

    public function handle(DevolucionRegistrada $event): void
    {
        foreach ($event->items as $item) {
            $gondola = $this->gondolas->find($item->productoId());

            if ($gondola !== null) {
                $gondola->restock($item->cantidad());
                $this->gondolas->save($gondola);
            }

            $this->movimientos->save(new MovimientoDeStock(
                id: Str::uuid()->toString(),
                productoId: $item->productoId(),
                tipo: TipoDeMovimiento::Devolucion,
                cantidad: $item->cantidad(),
                ubicacion: UbicacionDeStock::Gondola,
                fecha: $this->clock->now(),
                referencia: $event->ventaId,
            ));
        }
    }
}
