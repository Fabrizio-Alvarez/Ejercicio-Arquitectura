<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Infrastructure\Persistence\EloquentMovimientoDeStockRepository;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Catalogo\OfertaRepository;
use Supermercado\Infrastructure\Persistence\EloquentProductoRepository;
use Supermercado\Infrastructure\Persistence\EloquentOfertaRepository;
use Supermercado\Domain\Ventas\VentaRepository;
use Supermercado\Infrastructure\Persistence\EloquentVentaRepository;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Infrastructure\Persistence\EloquentGondolaRepository;
use Supermercado\Infrastructure\Persistence\EloquentDepositoRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductoRepository::class, EloquentProductoRepository::class);
        $this->app->bind(OfertaRepository::class, EloquentOfertaRepository::class);
        $this->app->bind(VentaRepository::class, EloquentVentaRepository::class);
        $this->app->bind(GondolaRepository::class, EloquentGondolaRepository::class);
        $this->app->bind(DepositoRepository::class, EloquentDepositoRepository::class);
        $this->app->bind(MovimientoDeStockRepository::class, EloquentMovimientoDeStockRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
