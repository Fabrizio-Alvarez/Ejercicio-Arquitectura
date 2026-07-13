<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Supermercado\Application\Stock\RegistrarReabastecimiento;

/**
 * CLI del depositista: recibe stock de un proveedor y sube el nivel del
 * depósito. Resuelve las alertas de depósito bajo (< 150).
 */
final class RestockDepositoCommand extends Command
{
    protected $signature = 'stock:restock
                            {productId : Producto a reabastecer}
                            {cantidad : Unidades recibidas del proveedor}
                            {--proveedor= : Proveedor del reabastecimiento (referencia opcional)}';

    protected $description = 'Restock a product\'s warehouse from a supplier (reabastecimiento del depósito)';

    public function handle(RegistrarReabastecimiento $restock): int
    {
        try {
            $outcome = $restock->execute(
                (string) $this->argument('productId'),
                (int) $this->argument('cantidad'),
                ($pro = $this->option('proveedor')) !== false && $pro !== '' ? (string) $pro : null,
            );
        } catch (\DomainException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Reabastecido: +{$outcome->recibido} unidades de {$outcome->productId}. Depósito ahora en {$outcome->nivelDelDeposito}.");

        return self::SUCCESS;
    }
}
