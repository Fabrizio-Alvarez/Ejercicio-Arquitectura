<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

use Illuminate\Support\Str;
use Supermercado\Domain\Comun\Clock;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\MovimientoDeStock;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;

/**
 * Use case: RegistrarAjuste.
 *
 * Corrige manualmente el stock de un producto en una ubicación concreta
 * (góndola o depósito). El delta puede ser positivo (sumar unidades, p. ej.
 * tras un conteo que arroja más stock del esperado) o negativo (restar,
 * mermas / roturas). Se registra como movimiento de auditoría con el motivo
 * del ajuste como referencia.
 */
final class RegistrarAjuste
{
    public function __construct(
        private readonly GondolaRepository $gondolas,
        private readonly DepositoRepository $depositos,
        private readonly MovimientoDeStockRepository $movimientos,
        private readonly Clock $clock,
    ) {}

    public function execute(
        string $productId,
        UbicacionDeStock $ubicacion,
        int $delta,
        ?string $motivo = null,
    ): MovimientoDeStock {
        if ($delta === 0) {
            throw new \DomainException("El delta del ajuste no puede ser cero para el producto {$productId}.");
        }

        $abs = abs($delta);

        match ($ubicacion) {
            UbicacionDeStock::Gondola => $this->ajustarGondola($productId, $delta, $abs),
            UbicacionDeStock::Deposito => $this->ajustarDeposito($productId, $delta, $abs),
        };

        $movimiento = new MovimientoDeStock(
            id: Str::uuid()->toString(),
            productoId: $productId,
            tipo: TipoDeMovimiento::Ajuste,
            cantidad: $abs,
            ubicacion: $ubicacion,
            fecha: $this->clock->now(),
            referencia: $motivo,
        );

        $this->movimientos->save($movimiento);

        return $movimiento;
    }

    private function ajustarGondola(string $productId, int $delta, int $abs): void
    {
        $gondola = $this->gondolas->find($productId)
            ?? throw new \DomainException("No stock is being tracked for product {$productId}.");

        $delta > 0 ? $gondola->restock($abs) : $gondola->descontar($abs);
        $this->gondolas->save($gondola);
    }

    private function ajustarDeposito(string $productId, int $delta, int $abs): void
    {
        $deposito = $this->depositos->find($productId)
            ?? throw new \DomainException("No stock is being tracked for product {$productId}.");

        $delta > 0 ? $deposito->receive($abs) : $deposito->take($abs);
        $this->depositos->save($deposito);
    }
}
