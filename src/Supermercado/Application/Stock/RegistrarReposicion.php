<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Supermercado\Domain\Comun\Clock;
use Supermercado\Domain\Stock\UbicacionDeStock;
use Supermercado\Domain\Stock\PoliticaDeReposicion;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\MovimientoDeStock;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Stock\TipoDeMovimiento;

/**
 * Use case: RegistrarReposicion (+ EmitirAlerta).
 *
 * A stock clerk replenishes a product's shelf from the warehouse. The
 * replenishment policy decides how much to move (and whether to emit a
 * low-stock alert). The use case applies the move, persists the new shelf
 * and warehouse levels, and returns the outcome (including the alert, if any).
 */
final class RegistrarReposicion
{
    public function __construct(
        private readonly GondolaRepository $shelves,
        private readonly DepositoRepository $warehouses,
        private readonly PoliticaDeReposicion $policy,
        private readonly MovimientoDeStockRepository $movimientos,
        private readonly Clock $clock,
    ) {}

    public function execute(string $productId): ResultadoDeReposicion
    {
        $shelf = $this->shelves->find($productId);
        $warehouse = $this->warehouses->find($productId);

        if ($shelf === null || $warehouse === null) {
            throw new \DomainException("No stock is being tracked for product {$productId}.");
        }

        $result = $this->policy->decide($shelf, $warehouse);

        if ($result->hasReplenishment()) {
            $shelf->restock($result->quantityToMove());
            $warehouse->take($result->quantityToMove());

            $this->shelves->save($shelf);
            $this->warehouses->save($warehouse);

            // El depósito deja huella de la reposición (auditoría).
            $this->movimientos->save(new MovimientoDeStock(
                id: Str::uuid()->toString(),
                productoId: $productId,
                tipo: TipoDeMovimiento::Reposicion,
                cantidad: $result->quantityToMove(),
                ubicacion: UbicacionDeStock::Gondola,
                fecha: $this->clock->now(),
            ));
        }

        $alert = $result->emitsAlert()
            ? new AlertaDeStock($productId, UbicacionDeStock::Deposito, $warehouse->quantity(), $this->clock->now())
            : null;
        if ($alert !== null) {
            Event::dispatch($alert);
        }

        return new ResultadoDeReposicion($result, $alert);
    }
}
