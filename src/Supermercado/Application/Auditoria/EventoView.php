<?php

declare(strict_types=1);

namespace Supermercado\Application\Auditoria;

/**
 * DTO de solo lectura: un evento de dominio persistido, listo para
 * serializar como prop de Inertia o respuesta JSON.
 */
final class EventoView
{
    /**
     * @param array<string, mixed>|null $payload
     */
    public function __construct(
        public readonly int $id,
        public readonly string $tipo,
        public readonly ?array $payload,
        public readonly string $occurredAt,
    ) {}
}
