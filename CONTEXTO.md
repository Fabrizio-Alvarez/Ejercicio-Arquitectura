# 📋 CONTEXTO.md — Supermarket Stock (Laravel + DDD)

**Para retomar este proyecto en otra sesión.** Self-contained: con este archivo + el repo, cualquier sesión puede continuar.

---

## Qué es

Backend de gestión de stock de supermercado en **Laravel 13 + PHP 8.4** con **arquitectura DDD/hexagonal** estricta. Requerimientos de un spec real (`docs/specs/`). El **headline**: el dominio es PHP puro, testeado con Pest **sin Laravel ni DB** — prueba que la frontera hexagonal es real.

## Stack y versiones

- Laravel **13** · PHP **8.4** (obligatorio: symfony 8.1 requiere >=8.4) · Pest · Playwright (declarado) · GitHub Actions · Docker.
- DB: **SQLite** (dev + tests `:memory:`). Postgres declarado para deploy.
- Auth: Sanctum (instalado; endpoints actualmente públicos — auth opcional per spec).

## Arquitectura (capas, dependencia siempre hacia adentro)

```
src/Supermarket/
  Domain/            # PURO PHP, sin Laravel. 48 unit tests, sin DB.
    Catalog/   Product, Offer, ProductRepository (port), OfferRepository (port, read-only)
    Sales/     Sale (aggregate + reconstitute), SaleLine, SaleStatus (enum),
               Pricer (domain service), CashClose, SaleSummary, SaleRepository (port)
    Stock/     Shelf, Warehouse, ReplenishmentPolicy, ReplenishmentResult, StockAlert,
               ShelfRepository (port), WarehouseRepository (port)
    Shared/    Money (VO, integer cents), CurrencyMismatchException
  Application/       # Casos de uso (orquestan dominio + puertos)
    Sales/     CobrarProductos (+ CobrarRequest, ItemRequest, ProductNotFoundException),
               ObtenerCierreDeCaja
    Stock/     ListarStock (+ StockView), RegistrarReposicion (+ ReposicionOutcome)
  Infrastructure/Persistence/   # Adapters Eloquent de los puertos del dominio
    ProductModel, SaleModel, SaleLineModel, ShelfModel, WarehouseModel, OfferModel
    Eloquent{Product,Sale,Offer,Shelf,Warehouse}Repository
app/
  Http/Controllers/Api/   Checkout, CashClose, Stock, Replenishment (single-action __invoke)
  Console/Commands/       ReplenishStockCommand (CLI del repositor)
  Providers/AppServiceProvider  # binds puertos → adapters (acá se cablea el hexagonal)
routes/api.php            # /checkout, /cash-close, /stock, /replenish/{id}
```

## Los 6 casos de uso del spec (todos hechos + testeados)

1. Cálculo de precios con ofertas → `Pricer` (mejor oferta activa, time-windowed).
2. Registro de ventas → `Sale` aggregate (state machine, currency invariant, freeze de precio).
3. Cierre de caja → `CashClose::forCashierOn` (filtra confirmadas por cajera/día).
4. Listado de stock → `ListarStock`.
5. Reposición → `RegistrarReposicion` + `ReplenishmentPolicy` (<30 → 50, capped por warehouse).
6. Alerta de stock → `StockAlert` cuando warehouse proyectado < 150.

## Cómo correr / testear / deployar

**No hay PHP/composer nativos en Windows.** Se desarrolla via Docker con la imagen `composer:latest`:
```bash
docker() { "C:\Program Files\Docker\Docker\resources\bin\docker.exe" "$@"; }
mkdir -p .docker-tmp && printf '{}\n' > .docker-tmp/config.json   # bypass credential helper
export DOCKER_CONFIG="$PWD/.docker-tmp"
docker run --rm -v "$PWD:/var/www/html" -w /var/www/html composer:latest php vendor/bin/pest   # tests
docker run --rm -v "$PWD:/var/www/html" -w /var/www/html composer:latest php artisan migrate:fresh --seed   # seed demo
```
- **Tests:** `vendor/bin/pest`. Unit (dominio puro, ~0s) + Feature (persistencia/app/HTTP, con RefreshDatabase + sqlite `:memory:`). ~77 tests, todos verde. CI verde en GitHub.
- **Deploy:** `Dockerfile` (single container, `php artisan migrate --seed` + `php artisan serve`, lee `PORT`). Railway: ver sección Deploy del README. Seed demo en `database/seeders/DatabaseSeeder.php`.

## ⚠️ Gotchas (para no tropezar al retomar)

- **Docker Desktop + mount a FS Windows = LENTO** (~30-90s por op). Paciencia o instalar PHP nativo.
- **PHP 8.4 obligatorio.** El lock se generó con `composer:latest` (PHP 8.4). No bajar a 8.3.
- **CI sin `.env`** → `phpunit.xml` setea `APP_KEY` (sin eso, `MissingAppKeyException` en feature tests).
- **`optimize-autoloader: false`** en composer.json — con `true`, el `dump-autoload` era lentísimo en el mount.
- **PSR-4:** `"Supermarket\\": "src/Supermarket/"` (el prefijo se descarta → la clase `Supermarket\Domain\X` vive en `src/Supermarket/Domain/X.php`).
- **`tests/Pest.php`** con `uses(Tests\TestCase::class)->in('Feature');` es **obligatorio** para que los feature tests booteen el app y los bindings del AppServiceProvider resuelvan.
- **`.docker-tmp/`** está gitignored (es solo el bypass local del credential helper).

## Estado y próximos pasos

- ✅ Dominio + Infrastructure + Application + Presentation + Dockerfile + CI + seeder + README + Deploy section. **CI verde.**
- ⏳ **Deploy** (Railway) — falta el click del usuario (su cuenta).
- 🔜 Futuro (no bloqueante): domain events formales (ahora el StockAlert es in-memory), auth real en endpoints, frontend mínimo, portear a Postgres para deploy, más feature tests de edge cases.
