<?php

declare(strict_types=1);

namespace Supermercado\Application\Stock;

use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\GondolaRepository;

/**
 * Configura los umbrales de alerta de stock bajo para un producto,
 * tanto en góndola como en depósito. Los parámetros son opcionales:
 * solo se actualizan los que se proporcionan (el otro se mantiene).
 *
 * Requiere que el producto ya tenga stock rastreado (góndola/depósito
 * existentes); si no existe, lanza DomainException — igual que los
 * demás use cases de stock.
 */
final class ConfigurarUmbrales
{
    public function __construct(
        private readonly GondolaRepository $gondolas,
        private readonly DepositoRepository $depositos,
    ) {}

    public function execute(string $productId, ?int $umbralGondola, ?int $umbralDeposito): void
    {
        if ($umbralGondola !== null) {
            $gondola = $this->gondolas->find($productId)
                ?? throw new \DomainException("No stock is being tracked for product {$productId}.");
            $gondola->configurarUmbral($umbralGondola);
            $this->gondolas->save($gondola);
        }

        if ($umbralDeposito !== null) {
            $deposito = $this->depositos->find($productId)
                ?? throw new \DomainException("No stock is being tracked for product {$productId}.");
            $deposito->configurarUmbral($umbralDeposito);
            $this->depositos->save($deposito);
        }
    }
}
