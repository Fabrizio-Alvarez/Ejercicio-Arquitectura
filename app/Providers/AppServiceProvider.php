<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Supermarket\Domain\Catalog\ProductRepository;
use Supermarket\Domain\Catalog\OfferRepository;
use Supermarket\Infrastructure\Persistence\EloquentProductRepository;
use Supermarket\Infrastructure\Persistence\EloquentOfferRepository;
use Supermarket\Domain\Sales\SaleRepository;
use Supermarket\Infrastructure\Persistence\EloquentSaleRepository;
use Supermarket\Domain\Stock\ShelfRepository;
use Supermarket\Domain\Stock\WarehouseRepository;
use Supermarket\Infrastructure\Persistence\EloquentShelfRepository;
use Supermarket\Infrastructure\Persistence\EloquentWarehouseRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
        $this->app->bind(OfferRepository::class, EloquentOfferRepository::class);
        $this->app->bind(SaleRepository::class, EloquentSaleRepository::class);
        $this->app->bind(ShelfRepository::class, EloquentShelfRepository::class);
        $this->app->bind(WarehouseRepository::class, EloquentWarehouseRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
