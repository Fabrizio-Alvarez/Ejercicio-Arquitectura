<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure;

use Supermercado\Domain\Comun\Clock;

/**
 * Reloj fijo para tests: siempre devuelve el mismo instante. Permite escribir
 * aserciones deterministas sobre marcas temporales (ventas, movimientos,
 * alertas) sin depender del reloj real ni de esperas (sleep).
 */
final class FixedClock implements Clock
{
    public function __construct(private readonly \DateTimeImmutable $fijado) {}

    public function now(): \DateTimeImmutable
    {
        return $this->fijado;
    }
}
