<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

use Illuminate\Support\Str;
use Supermercado\Domain\Comun\Clock;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\MovimientoDeStock;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;

/**
 * Use case: RegistrarReabastecimiento.
 *
 * El depósito recibe stock de un proveedor. A diferencia de la reposición de
 * góndola (automática, desde el depósito), el reabastecimiento es una decisión
 * humana —resuelve la alerta de depósito bajo— y por eso se invoca de forma
 * explícita (API, CLI o vista). El depósito sube su nivel y deja huella del
 * movimiento (auditoría) con el proveedor como referencia opcional.
 */
final class RegistrarReabastecimiento
{
    public function __construct(
        private readonly DepositoRepository $warehouses,
        private readonly MovimientoDeStockRepository $movimientos,
        private readonly Clock $clock,
    ) {}

    public function execute(string $productId, int $cantidad, ?string $proveedor = null): ResultadoDeReabastecimiento
    {
        if ($cantidad <= 0) {
            throw new \DomainException("La cantidad a reabastecer debe ser positiva para el producto {$productId}.");
        }

        $warehouse = $this->warehouses->find($productId);

        if ($warehouse === null) {
            throw new \DomainException("No stock is being tracked for product {$productId}.");
        }

        $warehouse->receive($cantidad);
        $this->warehouses->save($warehouse);

        $this->movimientos->save(new MovimientoDeStock(
            id: Str::uuid()->toString(),
            productoId: $productId,
            tipo: TipoDeMovimiento::Reabastecimiento,
            cantidad: $cantidad,
            ubicacion: UbicacionDeStock::Deposito,
            fecha: $this->clock->now(),
            referencia: $proveedor,
        ));

        return new ResultadoDeReabastecimiento($productId, $cantidad, $warehouse->quantity());
    }
}
