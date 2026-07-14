<?php

declare(strict_types=1);

namespace Supermercado\Application\Ventas;

use Illuminate\Support\Facades\Event;
use Supermercado\Domain\Catalogo\OfertaRepository;
use Supermercado\Domain\Catalogo\Ofertas;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Clock;
use Supermercado\Domain\Ventas\Carrito;
use Supermercado\Domain\Ventas\Cotizador;
use Supermercado\Domain\Ventas\ItemCarrito;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\VentaRepository;

/**
 * Caso de uso: CobrarProductos.
 *
 * Un cajero registra los productos que un cliente lleva a la caja. El sistema
 * resuelve productos y ofertas, los carga en un Carrito del dominio, y éste
 * ensambla la Venta aplicando pricing vía Cotizador. El caso de uso confirma y
 * persiste la venta, y despacha el evento CompraRealizada para que reaccionen
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

        // Resolver productos y ofertas → items del carrito (dominio puro).
        $items = [];
        foreach ($request->items as $item) {
            $product = $this->products->find($item->productId);

            if ($product === null) {
                throw ProductoNoEncontradoException::forId($item->productId);
            }

            $ofertas = new Ofertas($this->offers->findByProduct($item->productId));

            $items[] = new ItemCarrito($product, $item->quantity, $ofertas);
        }

        // El carrito ensambla la venta aplicando pricing vía Cotizador.
        $carrito = new Carrito($items);
        $sale = $carrito->cobrar(
            $this->pricer, $now,
            $request->saleId, $request->cashierId, $request->customerName,
            $request->metodoDePago,
        );

        $sale->confirm();

        $this->sales->save($sale);

        // Despacha los eventos de dominio grabados por el agregado.
        foreach ($sale->eventos() as $evento) {
            Event::dispatch($evento);
        }

        return $sale;
    }
}
