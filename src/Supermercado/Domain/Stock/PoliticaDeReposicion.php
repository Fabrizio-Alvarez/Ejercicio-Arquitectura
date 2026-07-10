<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

/**
 * Domain service implementing the supermarket's replenishment rule:
 *
 *   "When a shelf has fewer than 30 units, the stock clerk replenishes it
 *    up to 50, taking units from the warehouse. If, after replenishing,
 *    the warehouse falls below 150, a low-stock alert must be emitted."
 *
 * decide() is PURE: it inspects the current shelf/warehouse state and
 * returns what WOULD happen, without mutating anything. The application
 * layer applies the result and emits the alert if requested.
 */
final class PoliticaDeReposicion
{
    /**
     * Nivel objetivo al que se repone la góndola. Es decisión de POLÍTICA, no
     * de la ubicación: "cuando la góndola está baja, llénala hasta 50". Los
     * umbrales de "baja" (30 góndola / 150 depósito) viven en cada entidad.
     */
    public const TARGET_LEVEL = 50;

    public function decide(Gondola $shelf, Deposito $warehouse): DecisionDeReposicion
    {
        // Le preguntamos a la góndola si necesita reposición (ella conoce su
        // umbral); no inspeccionamos su cantidad desde afuera.
        if (! $shelf->isLow()) {
            return DecisionDeReposicion::none($shelf->productId());
        }

        // Cuánto falta para llegar al objetivo, acotado a lo que el depósito
        // puede entregar; y si eso lo dejaría bajo, emitimos alerta.
        $toMove = $warehouse->maxAvailableFor($shelf->gapTo(self::TARGET_LEVEL));

        return new DecisionDeReposicion(
            $shelf->productId(),
            $toMove,
            $warehouse->wouldBeLowAfter($toMove),
        );
    }
}
