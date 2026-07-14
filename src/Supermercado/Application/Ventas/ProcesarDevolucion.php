<?php

declare(strict_types=1);

namespace Supermercado\Application\Ventas;

use Supermercado\Domain\Ventas\ItemDevolucion;
use Supermercado\Domain\Ventas\VentaRepository;
use Illuminate\Support\Facades\Event;

/**
 * Caso de uso: ProcesarDevolucion.
 *
 * Carga una venta confirmada, registra los items devueltos en el agregado,
 * persiste el resultado y despacha el evento DevolucionRegistrada para que
 * los listeners restauren stock en la góndola y dejen huella en el depósito.
 */
final class ProcesarDevolucion
{
    public function __construct(
        private readonly VentaRepository $sales,
    ) {}

    /**
     * @param  ItemDevolucion[]  $items
     */
    public function execute(string $ventaId, array $items): void
    {
        $venta = $this->sales->find($ventaId);

        if ($venta === null) {
            throw VentaNoEncontradaException::forId($ventaId);
        }

        $venta->registrarDevolucion($items);

        $this->sales->save($venta);

        foreach ($venta->eventos() as $evento) {
            Event::dispatch($evento);
        }
    }
}
