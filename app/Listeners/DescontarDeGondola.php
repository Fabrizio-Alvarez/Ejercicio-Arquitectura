<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Event;
use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Comun\Clock;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\UbicacionDeStock;
use Supermercado\Domain\Ventas\CompraRealizada;

/**
 * Reacciona a CompraRealizada: descuenta de la góndola las unidades vendidas
 * ("se descuenta del stock de la góndola"). Si la góndola cae bajo su mínimo,
 * emite una AlertaDeStock para que reaccione el repositor.
 */
final class DescontarDeGondola
{
    public function __construct(private readonly GondolaRepository $gondolas, private readonly Clock $clock) {}

    public function handle(CompraRealizada $event): void
    {
        foreach ($event->lineas as $linea) {
            $gondola = $this->gondolas->find($linea->productId());

            // Si no se trackea stock de exhibición para este producto, no hay nada que descontar.
            if ($gondola === null) {
                continue;
            }

            $gondola->confirmarReserva($linea->quantity());
            $this->gondolas->save($gondola);

            if ($gondola->isLow()) {
                Event::dispatch(new AlertaDeStock(
                    $linea->productId(),
                    UbicacionDeStock::Gondola,
                    $gondola->quantity(),
                    $this->clock->now(),
                ));
            }
        }
    }
}
