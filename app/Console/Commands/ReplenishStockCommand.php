<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Supermarket\Application\Stock\RegistrarReposicion;

/**
 * Repositor CLI: the spec says stock clerks use a terminal to register
 * replenishment. This command runs the RegistrarReposicion use case.
 */
final class ReplenishStockCommand extends Command
{
    protected $signature = 'stock:replenish {productId : The product to replenish}';

    protected $description = 'Replenish a product\'s shelf from the warehouse (repositor)';

    public function handle(RegistrarReposicion $replenish): int
    {
        $outcome = $replenish->execute((string) $this->argument('productId'));

        if (! $outcome->result->hasReplenishment()) {
            $this->info('Shelf is healthy; nothing to replenish.');

            return self::SUCCESS;
        }

        $this->info("Replenished {$outcome->result->quantityToMove()} units onto the shelf.");

        if ($outcome->alert !== null) {
            $this->warn("STOCK ALERT: warehouse for {$outcome->alert->productId()} is low ({$outcome->alert->warehouseQuantity()} units).");
        }

        return self::SUCCESS;
    }
}
