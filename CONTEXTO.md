# 📋 CONTEXTO.md — Supermercado Stock (Laravel + DDD + Eventos + Vue)

**Para retomar este proyecto en otra sesión.** Self-contained: con este archivo + el repo, cualquier sesión puede continuar.

---

## Qué es

Backend de gestión de stock de supermercado en **Laravel 13 + PHP 8.4** con **arquitectura DDD/hexagonal estricta**, **eventos de dominio** (flujo de compra → depósito → repositor) y un **frontend Vue 3 + Inertia.js**. El dominio es PHP puro, testeado con Pest **sin Laravel ni DB** — prueba que la frontera hexagonal es real.

Todo el código de dominio/aplicación está **en español** (clases, namespaces, tablas); se conservan los términos propios de buenas prácticas (`Repository`, `Controller`, `Model`, `Middleware`, `Event` vía `Event::dispatch`).

## Stack y versiones

- Laravel **13** · PHP **8.4** (obligatorio) · Pest · Inertia.js 3 · Vue 3 · Vite 8 · Tailwind 4 · Docker · GitHub Actions.
- DB: **SQLite** (dev + tests `:memory:`). Postgres declarado para deploy.
- Auth: Sanctum (instalado; endpoints públicos). Acceso web por **selector de perfiles** (sin login): cajero / depositista / repositor.

## Arquitectura (capas, dependencia siempre hacia adentro)

```
src/Supermercado/
  Domain/            # PURO PHP, sin Laravel. Tests unitarios sin DB.
    Catalogo/ Producto, Oferta, Ofertas (colección first-class), ProductoRepository (port), OfertaRepository (port)
    Ventas/   Venta (aggregate: state machine + predicados isConfirmed/isForCashier/isOnDay +
              counts lineCount/itemCount), LineaDeVenta, EstadoDeVenta,
              Cotizador (servicio delgado, delega en Ofertas), CierreDeCaja, ResumenDeVenta,
              VentaRepository, MetodoDePago (enum), CompraRealizada (evento de dominio)
    Stock/    Gondola (UMBRAL_BAJO + gapTo), Deposito (UMBRAL_BAJO + maxAvailableFor/wouldBeLowAfter),
              PoliticaDeReposicion (orquestador delgado, TARGET_LEVEL), DecisionDeReposicion (+ none),
              AlertaDeStock (valor + evento), UbicacionDeStock (enum), TipoDeMovimiento (enum),
              MovimientoDeStock (auditoría), MovimientoDeStockRepository (port),
              GondolaRepository (port), DepositoRepository (port)
    Comun/    Dinero (VO, integer cents, +sum), MonedaDistintaException
  Application/       # Casos de uso (orquestan dominio + puertos)
    Ventas/   CobrarProductos (+ CobrarRequest, ItemRequest, ProductoNoEncontradoException),
              ObtenerCierreDeCaja
    Stock/    ListarStock (+ VistaDeStock), RegistrarReposicion (+ ResultadoDeReposicion),
              ListarMovimientos (+ MovimientoView)
  Infrastructure/Persistence/   # Adapters Eloquent de los puertos del dominio
    ProductoModel, VentaModel, LineaDeVentaModel, GondolaModel, DepositoModel, OfertaModel,
    MovimientoDeStockModel
    Eloquent{Producto,Oferta,Venta,Gondola,Deposito,MovimientoDeStock}Repository
app/
  Access/                Perfil (enum puro: cajero/depositista/repositor), SesionDePerfil
  Facades/               Perfil (Facade de acceso al perfil actual)
  Http/Controllers/Api/  CobroController, CierreDeCajaController, StockController, ReposicionController
  Http/Controllers/Web/  PaginaWebController (Inertia: stock/cobrar/movimientos + selector /iniciar)
  Http/Middleware/       HandleInertiaRequests, RequierePerfil (gatea vistas por perfil)
  Listeners/             DescontarDeGondola, AvisarAlDeposito, Repositor  (auto-discovery)
  Console/Commands/      ReponerStockCommand (CLI del repositor)
  Providers/AppServiceProvider  # binds puertos → adapters + 'sesion.de.perfil'
routes/api.php   # /checkout, /cash-close, /stock, /replenish/{id}
routes/web.php   # /iniciar (selector), /salir; grupo gateado 'perfil': /, /cobrar, /stock, /movimientos
resources/js/    # app.js, Layouts/AppLayout.vue (nav por rol), Paginas/{Stock,Cobrar,Movimientos,Perfiles/Iniciar}.vue
```

## Modelo de dominio rico (Tell, Don't Ask)

La lógica vive en las **entidades**, no en servicios anémicos: la capa de aplicación *les pregunta*
a los objetos en lugar de inspeccionarlos y recalcular.

- `Gondola`/`Deposito` son dueñas de su `UMBRAL_BAJO` y exponen sus operaciones (`gapTo`,
  `maxAvailableFor`, `wouldBeLowAfter`). `PoliticaDeReposicion::decide()` es un orquestador delgado
  que delega en ellas; sólo retiene `TARGET_LEVEL` (decisión de política, no de la ubicación).
- `Venta` expone sus reglas (`isConfirmed`, `isForCashier`, `isOnDay`) y contadores (`lineCount`,
  `itemCount`); `CierreDeCaja` los usa en vez de filtrar a mano.
- `Ofertas` es una colección first-class (`bestActiveFor`); `Cotizador` queda delgado.
- `Dinero::sum()` elimina el fold manual repetido en los totales.

## Flujo de eventos (compra → depósito → repositor)

```
POST /checkout → CobrarProductos → Venta::confirm() graba CompraRealizada
                                   ↓ Event::dispatch (tras persistir)
                ┌──────────────────┴──────────────────┐
        DescontarDeGondola                     AvisarAlDeposito
        descuenta la góndola (sale el          registra MovimientoDeStock
        producto vendido)                      (auditoría del depósito: "el
                │                              depósito avisa a su repositorio")
                │ si gondola < 30
                ▼
        AlertaDeStock(gondola)
                │
                ▼
        Repositor (servicio, sin identidad): repone desde el depósito
        (PoliticaDeReposicion: <30 → llenar a 50; depósito <150 → alerta)
```

- La **Venta** es un aggregate: registra líneas, total, **método de pago** y estado; al confirmarse graba el evento.
- **DescontarDeGondola**: descuenta el stock de exhibición (de donde sale el producto).
- **AvisarAlDeposito**: el depósito deja huella del movimiento (no descuenta backstock en la venta).
- **AlertaDeStock**: evento cuando góndola o depósito caen bajo su mínimo.
- **Repositor**: servicio sin identidad que repone la góndola desde el depósito al recibir la alerta.

## Perfiles del frontend (selector, sin login)

- Tres perfiles, cada uno con su vista: **Cajero** → /cobrar, **Depositista** → /movimientos, **Repositor** → /stock.
- `/iniciar` es el selector; el perfil se persiste en sesión vía el **Facade `Perfil`** (`App\Access\SesionDePerfil`, binding `sesion.de.perfil`), sin login.
- El middleware `RequierePerfil` (alias `perfil`) gatea: sin perfil → /iniciar; ruta no permitida para el perfil → su home.
- `HandleInertiaRequests` comparte el perfil a todas las páginas; `AppLayout` pinta nav + indicador conscientes del rol.

## Los casos de uso del spec (hechos + testeados)

1. Cálculo de precios con ofertas → `Cotizador` (mejor oferta activa, time-windowed).
2. Registro de ventas → `Venta` aggregate (state machine, currency invariant, freeze de precio, **método de pago**).
3. Cierre de caja → `CierreDeCaja::forCashierOn`.
4. Listado de stock → `ListarStock`.
5. Reposición → `RegistrarReposicion` + `PoliticaDeReposicion` (<30 → 50, capped por depósito).
6. Alerta de stock → `AlertaDeStock` (al reposicionar, depósito <150; y ahora también al vender, góndola <30).

## Cómo correr / testear / deployar

**No hay PHP/composer nativos en Windows.** Se desarrolla via Docker:
```bash
docker() { "C:\Program Files\Docker\Docker\resources\bin\docker.exe" "$@"; }
docker run --rm -v "$PWD:/var/www/html" -w /var/www/html composer:latest php vendor/bin/pest
docker run --rm -v "$PWD:/var/www/html" -w /var/www/html composer:latest php artisan migrate:fresh --seed
```
- **Tests:** `vendor/bin/pest`. Unit (dominio puro) + Feature (persistencia/app/HTTP/eventos/web/perfiles). 94 tests, verde. CI verde.
- **Frontend:** `npm install && npm run build` (nativo; Node 24). Dev: `npm run dev` (Vite) + `php artisan serve`.
- **Deploy:** `Dockerfile` (single container, `php artisan migrate --seed` + `php artisan serve`, lee `PORT`). Seed demo en `database/seeders/DatabaseSeeder.php`.

## ⚠️ Gotchas

- **Docker Desktop + mount a FS Windows = MUY LENTO** (cold start ~60-90s; un test feature puede tardar 30-200s). Paciencia.
- **PHP 8.4 obligatorio.** No bajar a 8.3.
- **CI sin `.env`** → `phpunit.xml` setea `APP_KEY` (sino, `MissingAppKeyException` en feature tests).
- **PSR-4:** `"Supermercado\\": "src/Supermercado/"`.
- **Eventos:** los listeners en `app/Listeners` se cablean por **auto-discovery** de Laravel 11+ (NO hay `EventServiceProvider`; si se agrega uno, se duplican y disparan dos veces).
- **Inertia v3:** root view `resources/views/app.blade.php` (`@inertia`); middleware `HandleInertiaRequests` registrado en `bootstrap/app.php`.
- **`tests/Pest.php`** con `uses(Tests\TestCase::class)->in('Feature');` es obligatorio para que los feature tests booteen el app y resuelvan los bindings.

## Estado

- ✅ Dominio + Infrastructure + Application + Presentation (API + Web/Inertia) + Eventos + **selector de perfiles** + Vue + Dockerfile + CI + seeder + README. **94 tests verde. CI verde.**
- 🔜 Futuro: login + roles reales sobre los perfiles actuales, SSR de Inertia, portear a Postgres, más tests de edge cases.
