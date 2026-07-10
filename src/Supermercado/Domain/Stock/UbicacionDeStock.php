<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

/**
 * Dónde físicamente se encuentra el stock de un producto en el supermercado.
 */
enum UbicacionDeStock: string
{
    case Gondola = 'gondola';
    case Deposito = 'deposito';
}
