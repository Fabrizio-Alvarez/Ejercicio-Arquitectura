<?php

namespace App\Listeners;

use Illuminate\Support\Str;
use Supermercado\Domain\Comun\Clock;
use Supermercado\Domain\Stock\MovimientoDeStock;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;
use Supermercado\Domain\Ventas\CompraRealizada;

/**
 * El depósito es notificado de cada venta: registra un movimiento de stock
 * (auditoría) en su repositorio. "el depósito avisa a [su] repositorio".
 *
 * El backstock del depósito NO se descuenta con una venta (el producto sale de
 * la góndola); el depósito solo deja huella del movimiento para control.
 */
final class AvisarAlDeposito
{
    public function __construct(private readonly MovimientoDeStockRepository $movimientos, private readonly Clock $clock) {}

    public function handle(CompraRealizada $event): void
    {
        foreach ($event->lineas as $linea) {
            $this->movimientos->save(new MovimientoDeStock(
                id: Str::uuid()->toString(),
                productoId: $linea->productId(),
                tipo: TipoDeMovimiento::Venta,
                cantidad: $linea->quantity(),
                ubicacion: UbicacionDeStock::Gondola,
                fecha: $this->clock->now(),
                referencia: $event->ventaId,
            ));
        }
    }
}
