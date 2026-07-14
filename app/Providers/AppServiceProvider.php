<?php

namespace App\Providers;

use App\Listeners\RegistrarEventoDeDominio;
use Illuminate\Support\Facades\Event;
use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Ventas\CompraRealizada;
use App\Access\SesionDePerfil;
use Illuminate\Support\ServiceProvider;
use Supermercado\Domain\Stock\AlertaDeStockRepository;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Catalogo\OfertaRepository;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Ventas\VentaRepository;
use Supermercado\Domain\Comun\Clock;
use Supermercado\Infrastructure\Persistence\EloquentAlertaDeStockRepository;
use Supermercado\Infrastructure\Persistence\EloquentDepositoRepository;
use Supermercado\Infrastructure\Persistence\EloquentGondolaRepository;
use Supermercado\Infrastructure\Persistence\EloquentMovimientoDeStockRepository;
use Supermercado\Infrastructure\Persistence\EloquentOfertaRepository;
use Supermercado\Infrastructure\Persistence\EloquentProductoRepository;
use Supermercado\Infrastructure\Persistence\EloquentVentaRepository;
use Supermercado\Infrastructure\Persistence\JsonAlertaDeStockRepository;
use Supermercado\Infrastructure\Persistence\JsonDepositoRepository;
use Supermercado\Infrastructure\Persistence\JsonGondolaRepository;
use Supermercado\Infrastructure\Persistence\JsonMovimientoDeStockRepository;
use Supermercado\Infrastructure\Persistence\JsonOfertaRepository;
use Supermercado\Infrastructure\Persistence\JsonProductoRepository;
use Supermercado\Infrastructure\Persistence\JsonVentaRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Bindea cada puerto de repositorio del dominio a su adapter. El origen de
     * datos es configurable: Eloquent (SQLite/Postgres, por defecto) o Json
     * (archivos de texto plano en disco, como pide el spec no funcional).
     */
    public function register(): void
    {
        $json = config('supermercado.persistence') === 'json';

        $this->app->bind(ProductoRepository::class, $json ? JsonProductoRepository::class : EloquentProductoRepository::class);
        $this->app->bind(OfertaRepository::class, $json ? JsonOfertaRepository::class : EloquentOfertaRepository::class);
        $this->app->bind(VentaRepository::class, $json ? JsonVentaRepository::class : EloquentVentaRepository::class);
        $this->app->bind(GondolaRepository::class, $json ? JsonGondolaRepository::class : EloquentGondolaRepository::class);
        $this->app->bind(DepositoRepository::class, $json ? JsonDepositoRepository::class : EloquentDepositoRepository::class);
        $this->app->bind(MovimientoDeStockRepository::class, $json ? JsonMovimientoDeStockRepository::class : EloquentMovimientoDeStockRepository::class);
        $this->app->bind(AlertaDeStockRepository::class, $json ? JsonAlertaDeStockRepository::class : EloquentAlertaDeStockRepository::class);
        $this->app->singleton(\Supermercado\Domain\Comun\Clock::class, \Supermercado\Infrastructure\SystemClock::class);
        $this->app->singleton(\Supermercado\Domain\Ventas\PaymentGateway::class, \Supermercado\Infrastructure\Payments\AlwaysSucceedsPaymentGateway::class);

        $this->app->singleton('sesion.de.perfil', fn ($app) => new SesionDePerfil($app->make('session.store')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(CompraRealizada::class, RegistrarEventoDeDominio::class);
        Event::listen(AlertaDeStock::class, RegistrarEventoDeDominio::class);
    }
}
