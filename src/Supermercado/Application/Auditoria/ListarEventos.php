<?php

declare(strict_types=1);

namespace Supermercado\Application\Auditoria;

use Supermercado\Infrastructure\Persistence\EventoDeDominioModel;

/**
 * Use case: ListarEventos.
 *
 * Devuelve los eventos de dominio persistidos en el log de auditoría,
 * ordenados del más reciente al más antiguo (límite 200 para no cargar
 * toda la tabla en cada render).
 */
final class ListarEventos
{
    private const LIMITE = 200;

    /** @return EventoView[] */
    public function execute(): array
    {
        return EventoDeDominioModel::query()
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(self::LIMITE)
            ->get()
            ->map(static fn (EventoDeDominioModel $e) => new EventoView(
                $e->id,
                $e->tipo,
                $e->payload,
                $e->occurred_at->format('Y-m-d H:i:s'),
            ))
            ->all();
    }
}
