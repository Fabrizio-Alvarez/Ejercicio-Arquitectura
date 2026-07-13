<?php

declare(strict_types=1);

namespace Supermercado\Domain\Comun;

/**
 * Puerto del reloj del dominio.
 *
 * Separa la noción de "ahora" del reloj del sistema, de modo que los casos de
 * uso (y sus tests) sean deterministas respecto al tiempo: el dominio pide la
 * hora, la infraestructura la provee. En tests se inyecta un FixedClock.
 */
interface Clock
{
    public function now(): \DateTimeImmutable;
}
