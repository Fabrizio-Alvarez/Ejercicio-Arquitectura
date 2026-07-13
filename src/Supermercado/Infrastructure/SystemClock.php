<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure;

use Supermercado\Domain\Comun\Clock;

/**
 * Reloj real: delega al reloj del sistema. Es la implementación de producción
 * del puerto {@see \Supermercado\Domain\Comun\Clock}.
 */
final class SystemClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now');
    }
}
