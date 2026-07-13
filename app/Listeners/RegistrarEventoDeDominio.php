<?php

declare(strict_types=1);

namespace App\Listeners;

use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Ventas\CompraRealizada;
use Supermercado\Infrastructure\Persistence\EventoDeDominioModel;

/**
 * Persiste cada evento de dominio en la tabla eventos_de_dominio (log de
 * auditoría). Se registra manualmente en AppServiceProvider para escuchar
 * todos los eventos de dominio despachados por la capa de aplicación.
 *
 * Cada evento se serializa a un payload JSON con sus datos esenciales, de
 * forma que el log es navegable sin reconstruir el objeto completo.
 */
final class RegistrarEventoDeDominio
{
    public function handle(object $event): void
    {
        EventoDeDominioModel::create([
            'tipo' => $this->shortName($event),
            'payload' => $this->serialize($event),
            'occurred_at' => now(),
        ]);
    }

    private function shortName(object $event): string
    {
        $parts = explode('\\', $event::class);
        return array_pop($parts);
    }

    /** @return array<string, mixed> */
    private function serialize(object $event): array
    {
        if ($event instanceof CompraRealizada) {
            return [
                'ventaId' => $event->ventaId,
                'metodoDePago' => $event->metodoDePago->value,
                'lineas' => array_map(static fn ($l) => [
                    'productoId' => $l->productId(),
                    'cantidad' => $l->quantity(),
                ], $event->lineas),
            ];
        }

        if ($event instanceof AlertaDeStock) {
            return [
                'productoId' => $event->productId(),
                'ubicacion' => $event->ubicacion()->value,
                'cantidad' => $event->cantidad(),
            ];
        }

        return [];
    }
}
