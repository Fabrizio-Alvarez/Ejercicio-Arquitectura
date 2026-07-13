<?php

namespace App\Listeners;

use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Stock\AlertaDeStockRepository;

/**
 * Persiste cada alerta de stock bajo emitida por el dominio (sea de góndola
 * al vender o de depósito al reponer). El spec exige que las alertas de stock
 * se almacenen de manera persistente.
 *
 * Listener reactivo: no decide nada, sólo deja registro de cada alerta para
 * la gestión y auditoría del establecimiento.
 */
final class RegistrarAlerta
{
    public function __construct(private readonly AlertaDeStockRepository $alertas) {}

    public function handle(AlertaDeStock $event): void
    {
        $this->alertas->save($event);
    }
}
