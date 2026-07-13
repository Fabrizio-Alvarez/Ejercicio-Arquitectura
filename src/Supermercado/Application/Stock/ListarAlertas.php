<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

use Supermercado\Domain\Stock\AlertaDeStockRepository;

/**
 * Caso de uso: ListarAlertas.
 *
 * Devuelve el historial de alertas de stock bajo persistidas (de góndola al
 * vender y de depósito al reponer), de la más reciente a la más vieja.
 *
 * @return AlertaView[]
 */
final class ListarAlertas
{
    public function __construct(
        private readonly AlertaDeStockRepository $alertas,
    ) {}

    public function execute(): array
    {
        return array_map(AlertaView::from(...), $this->alertas->all());
    }
}
