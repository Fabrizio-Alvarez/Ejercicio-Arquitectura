<?php

declare(strict_types=1);

namespace Supermercado\Application\Ventas;

use Illuminate\Support\Facades\Event;
use Supermercado\Domain\Catalogo\OfertaRepository;
use Supermercado\Domain\Catalogo\Ofertas;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Clock;
use Supermercado\Domain\Ventas\Cotizador;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\VentaRepository;

/**
 * Caso de uso: CobrarProductos.
 *
 * Un cajero registra los productos que un cliente lleva a la caja. El sistema
 * cotiza cada uno (aplicando la mejor oferta activa), arma la Venta, la
 * confirma y la persiste. Al confirmarse, el agregado graba el evento de
 * dominio CompraRealizada; este caso de uso lo despacha para que reaccionen
 * los interesados (descontar la góndola, avisar al depósito, ...).
 */
final class CobrarProductos
{
    public function __construct(
        private readonly ProductoRepository $products,
        private readonly OfertaRepository $offers,
        private readonly VentaRepository $sales,
        private readonly Cotizador $pricer,
        private readonly Clock $clock,
    ) {}

    public function execute(CobrarRequest $request): Venta
    {
        $now = $this->clock->now();

        $sale = new Venta($request->saleId, $request->cashierId, $request->customerName, $now, $request->metodoDePago);

        foreach ($request->items as $item) {
            $product = $this->products->find($item->productId);

            if ($product === null) {
                throw ProductoNoEncontradoException::forId($item->productId);
            }

            $offers = new Ofertas($this->offers->findByProduct($item->productId));

            $sale->addLine($this->pricer->price($product, $item->quantity, $offers, $now));
        }

        $sale->confirm();

        $this->sales->save($sale);

        // Despacha los eventos de dominio grabados por el agregado.
        foreach ($sale->eventos() as $evento) {
            Event::dispatch($evento);
        }

        return $sale;
    }
}
