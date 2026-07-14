<?php

declare(strict_types=1);

namespace Supermercado\Application\Ventas;

use Illuminate\Support\Facades\Event;
use Supermercado\Domain\Catalogo\OfertaRepository;
use Supermercado\Domain\Catalogo\Ofertas;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Clock;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Ventas\Carrito;
use Supermercado\Domain\Ventas\Cotizador;
use Supermercado\Domain\Ventas\ItemCarrito;
use Supermercado\Domain\Ventas\PaymentGateway;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\VentaRepository;

/**
 * Caso de uso: CobrarProductos.
 *
 * Un cajero registra los productos que un cliente lleva a la caja. El sistema
 * resuelve productos y ofertas, los carga en un Carrito del dominio, y éste
 * ensambla la Venta aplicando pricing vía Cotizador. Reserva stock de góndola
 * mientras se procesa el pago; al confirmarse despacha CompraRealizada para que
 * reaccionen los interesados (confirmar reserva en góndola, avisar al depósito, ...).
 */
final class CobrarProductos
{
    public function __construct(
        private readonly ProductoRepository $products,
        private readonly OfertaRepository $offers,
        private readonly VentaRepository $sales,
        private readonly Cotizador $pricer,
        private readonly PaymentGateway $payments,
        private readonly GondolaRepository $gondolas,
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

        // Reservar stock de góndola mientras se procesa el pago.
        foreach ($sale->lines() as $linea) {
            $gondola = $this->gondolas->find($linea->productId());
            if ($gondola !== null) {
                $gondola->reservar($linea->quantity());
                $this->gondolas->save($gondola);
            }
        }

        // Cobrar el pago. Si se rechaza, la venta se cancela y se lanza.
        $resultado = $this->payments->charge($sale->total(), $sale->metodoDePago());

        if (! $resultado->exitoso) {
            // Liberar reservas: el pago falló, el stock vuelve a estar disponible.
            foreach ($sale->lines() as $linea) {
                $gondola = $this->gondolas->find($linea->productId());
                if ($gondola !== null) {
                    $gondola->liberarReserva($linea->quantity());
                    $this->gondolas->save($gondola);
                }
            }
            $sale->cancel();
            $this->sales->save($sale);
            throw PagoRechazadoException::forSale($sale->id());
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
