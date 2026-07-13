<?php

declare(strict_types=1);

namespace Supermercado\Domain\Stock;

/**
 * Repository port (hexagonal) para las alertas de stock bajo persistidas.
 *
 * El dominio define el contrato; la infraestructura provee el adapter. La
 * generación del id de persistencia es responsabilidad del adapter (la alerta
 * es un evento/valor del dominio, no un aggregate con identidad propia).
 */
interface AlertaDeStockRepository
{
    public function save(AlertaDeStock $alerta): void;

    /**
     * @return AlertaDeStock[]
     */
    public function all(): array;

    /**
     * @return AlertaDeStock[]
     */
    public function findByProducto(string $productoId): array;
}
