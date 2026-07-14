<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

use Supermercado\Domain\Comun\Dinero;

/**
 * Resultado de intentar cobrar un pago.
 *
 * Es un value object inmutable del dominio. El PaymentGateway devuelve una
 * instancia de esta clase; nunca lanza excepciones por pago rechazado —
 * el llamador decide qué hacer con un resultado fallido.
 */
final class ResultadoDePago
{
    private function __construct(
        public readonly bool $exitoso,
        public readonly ?string $referencia,
    ) {}

    public static function exitoso(string $referencia): self
    {
        return new self(true, $referencia);
    }

    public static function fallido(): self
    {
        return new self(false, null);
    }
}
