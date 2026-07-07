<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Supermarket\Domain\Catalog\ProductRepository;
use Supermarket\Infrastructure\Persistence\EloquentProductRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
