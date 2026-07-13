# 📋 CONTEXTO.md — Supermercado Stock (Laravel + DDD + Eventos + Vue)

**Para retomar este proyecto en otra sesión.** Self-contained: con este archivo + el repo, cualquier sesión puede continuar.

---

## Qué es

Backend de gestión de stock de supermercado en **Laravel 13 + PHP 8.4** con **arquitectura DDD/hexagonal estricta**, **eventos de dominio** (flujo de compra → depósito → repositor), **persistencia de alertas** y un **frontend Vue 3 + Inertia.js** con **login + roles reales**. El dominio es PHP puro, testeado con Pest **sin Laravel ni DB** — prueba que la frontera hexagonal es real.

Todo el código de dominio/aplicación está **en español** (clases, namespaces, tablas); se conservan los términos propios de buenas prácticas (`Repository`, `Controller`, `Model`, `Middleware`, `Event` vía `Event::dispatch`).

## Stack y versiones

- Laravel **13** · PHP **8.4** (obligatorio) · Pest · Inertia.js 3 · Vue 3 · Vite 8 · Tailwind 4 · Docker · GitHub Actions.
- DB: **SQLite** (dev + tests `:memory:`). **Postgres** declarado para deploy (perfil `docker compose --profile postgres`). **JSON en disco** como adapter alternativo (cumple el spec no funcional de "archivos de texto plano").
- Auth: **login web real** (users + roles mapeados a perfiles). Sanctum instalado para API.

## Arquitectura (capas, dependencia siempre hacia adentro)

```
src/Supermercado/
  Domain/            # PURO PHP, sin Laravel. Tests unitarios sin DB.
    Catalogo/ Producto, Oferta, Ofertas (colección first-class), ProductoRepository (port), OfertaRepository (port, read-only)
    Ventas/   Venta (aggregate: state machine + predicados isConfirmed/isForCashier/isOnDay +
              counts lineCount/itemCount), LineaDeVenta, EstadoDeVenta,
              Cotizador (servicio delgado, delega en Ofertas), CierreDeCaja, ResumenDeVenta,
              VentaRepository, MetodoDePago (enum), CompraRealizada (evento de dominio)
    Stock/    Gondola (UMBRAL_BAJO + gapTo), Deposito (UMBRAL_BAJO + maxAvailableFor/wouldBeLowAfter),
              PoliticaDeReposicion (orquestador delgado, TARGET_LEVEL), DecisionDeReposicion (+ none),
              AlertaDeStock (valor + evento), UbicacionDeStock (enum), TipoDeMovimiento (enum),
              MovimientoDeStock (auditoría), MovimientoDeStockRepository (port),
              AlertaDeStockRepository (port, persiste las alertas),
              GondolaRepository (port), DepositoRepository (port)
    Comun/    Dinero (VO, integer cents, +sum), MonedaDistintaException
  Application/       # Casos de uso (orquestan dominio + puertos)
    Ventas/   CobrarProductos (+ CobrarRequest, ItemRequest, ProductoNoEncontradoException),
              ObtenerCierreDeCaja
    Stock/    ListarStock (+ VistaDeStock), RegistrarReposicion (+ ResultadoDeReposicion),
              ListarMovimientos (+ MovimientoView), ListarAlertas (+ AlertaView)
  Infrastructure/Persistence/   # DOS adapters por cada port del dominio (frontera hexagonal honesta)
    Eloquent* (SQLite/Postgres) Y Json* (archivos de texto plano en disco)
    Models: ProductoModel, VentaModel, LineaDeVentaModel, GondolaModel, DepositoModel,
            OfertaModel, MovimientoDeStockModel, AlertaDeStockModel
    Trait: AlmacenaJson (I/O read/write de archivos JSON; dir base configurable)
    Eloquent{Producto,Oferta,Venta,Gondola,Deposito,MovimientoDeStock,AlertaDeStock}Repository
    Json{Producto,Oferta,Venta,Gondola,Deposito,MovimientoDeStock,AlertaDeStock}Repository
app/
  Access/                Perfil (enum puro: cajero/depositista/repositor), SesionDePerfil (deriva el perfil del usuario autenticado)
  Facades/               Perfil (Facade de acceso al perfil/usuario actual)
  Http/Controllers/Api/  CobroController, CierreDeCajaController, StockController, ReposicionController
  Http/Controllers/Web/  PaginaWebController (Inertia: stock/cobrar/cierre/movimientos/alertas + login/logout)
  Http/Middleware/       HandleInertiaRequests, RequierePerfil (gatea vistas por perfil del usuario autenticado)
  Listeners/             DescontarDeGondola, AvisarAlDeposito, Repositor, RegistrarAlerta (auto-discovery)
  Console/Commands/      ReponerStockCommand (CLI del repositor)
  Models/User            # rol → Perfil; Authenticatable para login web
  Providers/AppServiceProvider  # binds puertos → adapter (Eloquent por defecto, Json si SUPERMERCADO_PERSISTENCE=json) + 'sesion.de.perfil'
config/supermercado.php  # persistence (eloquent|json) + json_dir
routes/api.php   # /checkout, /cash-close, /stock, /replenish/{id}
routes/web.php   # /login (GET+POST), /logout; grupo gateado 'perfil': /, /cobrar, /cierre, /stock, /movimientos, /alertas
resources/js/    # app.js, Layouts/AppLayout.vue (nav por rol + logout), Paginas/{Stock,Cobrar,Cierre,Movimientos,Alertas,Perfiles/Login}.vue
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

## Flujo de eventos (compra → depósito → repositor → alerta persistida)

```
POST /checkout → CobrarProductos → Venta::confirm() graba CompraRealizada
                                   ↓ Event::dispatch (tras persistir)
                ┌──────────────────┴──────────────────┐
        DescontarDeGondola                     AvisarAlDeposito
        descuenta la góndola (sale el          registra MovimientoDeStock
        producto vendido)                      (auditoría del depósito)
                │ si gondola < 30
                ▼
        AlertaDeStock(gondola) ── Event::dispatch ──┐
                │                                   ▼
                ▼                            RegistrarAlerta (persiste la alerta)
        Repositor (servicio): repone         ↳ AlertaDeStockRepository
        desde el depósito                       (tabla alertas_de_stock / alertas.json)
        (PoliticaDeReposicion: <30 → llenar a 50; depósito <150 → alerta)
                │ si depósito < 150 (tras reponer)
                ▼
        AlertaDeStock(deposito) ── Event::dispatch ──→ RegistrarAlerta (persiste)
```

- La **Venta** es un aggregate: registra líneas, total, **método de pago** y estado; al confirmarse graba el evento.
- **DescontarDeGondola**: descuenta el stock de exhibición; si la góndola cae bajo su mínimo, despacha `AlertaDeStock`.
- **AvisarAlDeposito**: el depósito deja huella del movimiento (no descuenta backstock en la venta).
- **RegistrarReposicion**: aplica la reposición y, si el depósito cae bajo 150, **despacha** `AlertaDeStock` de depósito (antes sólo la devolvía como valor).
- **Repositor**: servicio sin identidad que repone la góndola desde el depósito al recibir la alerta de góndola.
- **RegistrarAlerta**: persiste cada `AlertaDeStock` (de góndola al vender y de depósito al reponer) — el spec exige *"incluidas las alertas de stock"*.
- **RegistrarReabastecimiento**: resuelve la alerta de depósito — el depositista recibe stock del proveedor (`Deposito::receive`), sube el nivel y deja huella (`MovimientoDeStock` tipo `Reabastecimiento`). Invocación manual (API `POST /api/restock`, CLI `stock:restock` o vista `/alertas`), a diferencia de la reposición automática del `Repositor`.
- **Clock**: puerto `Domain\Comun\Clock` (`now(): \DateTimeImmutable`) inyectado en use cases y listeners. `SystemClock` en producción; `FixedClock` en tests (determinista, sin `sleep`).

## Auth: login + roles reales

- Reemplazo del **selector de perfiles sin login** por **auth web real**: tabla `users` con campo `rol` (cajero/depositista/repositor).
- `App\Models\User::perfil()` mapea `rol` → enum `Perfil`. `SesionDePerfil::actual()` deriva el perfil del usuario autenticado (`Auth::user()`), no de la sesión.
- El **Facade `Perfil`** sigue siendo la API pública — controladores y middleware no cambiaron: `Perfil::actual()` / `Perfil::tiene()`.
- `/login` (GET form + POST `Auth::attempt`) y `/logout` (POST). Middleware `RequierePerfil` (alias `perfil`) gatea: sin sesión → /login; ruta no permitida para el rol → su home.
- `HandleInertiaRequests` comparte `perfil` y `usuario` a todas las páginas; `AppLayout` pinta nav + nombre + rol + botón **Cerrar sesión**.
- Seeder crea 3 users demo: `cajero@`/`depositista@`/`repositor@supermercado.test`, password `password`.

## API auth (Sanctum)

- `POST /api/tokens` valida email+password y emite un token Bearer. El token hereda el rol del usuario.
- Todos los endpoints `/api/*` están bajo `auth:sanctum` + middleware `rol:X` (alias registrado en `bootstrap/app.php`): `checkout`/`cash-close` → **cajero**, `stock`/`replenish` → **repositor**, `restock` → **depositista`.
- El frontend SPA (Inertia) se autentica vía sesión stateful (cookie del login web); clientes externos usan `Authorization: Bearer <token>`. 401 sin auth, 403 con rol incorrecto.

## Perfiles y sus vistas

- **Cajero** → `/tablero` (KPIs de ventas del día: total, ticket promedio, desglose por método de pago) + `/cobrar` (registrar venta) + `/cierre` (cierre de caja).
- **Depositista** → `/tablero` (alertas activas + movimientos recientes + reabastecimientos del día) + `/movimientos` (auditoría) + `/alertas` (historial).
- **Repositor** → `/tablero` (stock crítico + conteos de góndola/depósito bajo) + `/stock` (stock por producto).

## Los casos de uso del spec (hechos + testeados)

1. Cálculo de precios con ofertas → `Cotizador` (mejor oferta activa, time-windowed).
2. Registro de ventas → `Venta` aggregate (state machine, currency invariant, freeze de precio, **método de pago**).
3. Cierre de caja → `CierreDeCaja::forCashierOn` + vista `/cierre`.
4. Listado de stock → `ListarStock`.
5. Reposición → `RegistrarReposicion` + `PoliticaDeReposicion` (<30 → 50, capped por depósito).
6. Alerta de stock → `AlertaDeStock` **persistida** (`AlertaDeStockRepository` + listener `RegistrarAlerta`): góndola <30 al vender y depósito <150 al reponer. Vista `/alertas`.
7. Reabastecimiento del depósito → `RegistrarReabastecimiento` (resuelve alertas de depósito: recibe stock del proveedor, sube el nivel, registra `MovimientoDeStock` tipo `Reabastecimiento`). API `POST /api/restock`, CLI `stock:restock`, botón en la vista `/alertas`.
8. Tableros por rol → `ObtenerTableroCajero` (ventas del día), `ObtenerTableroDepositista` (alertas + movimientos), `ObtenerTableroRepositor` (stock crítico). Cada rol ve su dashboard consolidado en `/tablero` tras login.

## Adaptador JSON (frontera hexagonal honesta)

- Por cada port del dominio hay **dos adapters**: `Eloquent*Repository` (SQLite/Postgres) y `Json*Repository` (archivos de texto plano en disco).
- El trait `AlmacenaJson` provee el I/O (`leer()`/`escribir()`); cada adapter declara su archivo y su hidratación dominio↔fila.
- El binding se elige en `AppServiceProvider` según `config('supermercado.persistence')`: `'eloquent'` (default) o `'json'` (vía `SUPERMERCADO_PERSISTENCE=json`). Directorio base: `config('supermercado.json_dir')` (default `storage/app/supermercado`).
- **No se toca el dominio** al cambiar el origen de datos: es la prueba de que la frontera hexagonal es real.

## Cómo correr / testear / deployar

**No hay PHP/composer nativos en Windows.** Se desarrolla via Docker:
```bash
docker() { "C:\Program Files\Docker\Docker\resources\bin\docker.exe" "$@"; }

# SQLite (default):
docker compose run --rm app php vendor/bin/pest
docker compose run --rm app php artisan migrate:fresh --seed

# Postgres (perfil opt-in):
docker compose --profile postgres run --rm app-pg composer install
docker compose --profile postgres run --rm app-pg php artisan migrate --seed
#  (en .env: DB_CONNECTION=pgsql DB_HOST=postgres ...)

# Sobre JSON (cambia el origen a archivos de texto plano):
SUPERMERCADO_PERSISTENCE=json docker compose run --rm app php artisan migrate:fresh --seed
```
- **Tests:** `vendor/bin/pest`. Unit (dominio puro) + Feature (persistencia/app/HTTP/eventos/web/auth/JSON-adapters).
- **Frontend:** `npm install && npm run build` (nativo; Node 24). Dev: `npm run dev` (Vite) + `php artisan serve`.
- **Deploy:** `Dockerfile` (single container, `php artisan migrate --seed` + `php artisan serve`, lee `PORT`). Incluye `pdo_sqlite` + `pdo_pgsql`. Seed demo en `database/seeders/DatabaseSeeder.php`.

## ⚠️ Gotchas

- **Docker Desktop + mount a FS Windows = MUY LENTO** (cold start ~60-90s; un test feature puede tardar 30-200s). Paciencia.
- **PHP 8.4 obligatorio.** No bajar a 8.3.
- **CI sin `.env`** → `phpunit.xml` setea `APP_KEY` (sino, `MissingAppKeyException` en feature tests).
- **PSR-4:** `"Supermercado\\": "src/Supermercado/"`.
- **Eventos:** los listeners en `app/Listeners` se cablean por **auto-discovery** de Laravel 11+ (NO hay `EventServiceProvider`; si se agrega uno, se duplican y disparan dos veces).
- **Auth:** el perfil viene del usuario autenticado (`Auth::user()->perfil()`), no de la sesión. Si re-introducís un selector manual, hay que tocar `SesionDePerfil`.
- **Inertia v3:** root view `resources/views/app.blade.php` (`@inertia`); middleware `HandleInertiaRequests` registrado en `bootstrap/app.php`.
- **`tests/Pest.php`** con `uses(Tests\TestCase::class)->in('Feature');` + helpers `cajero()/depositista()/repositor()` es obligatorio para que los feature tests booteen el app y resuelvan los bindings.
- **JSON adapters:** no usan DB; los tests setean `config(['supermercado.json_dir' => $tmpDir])` en `beforeEach`.

## Estado

- ✅ Dominio + Infrastructure (Eloquent **+ Json**) + Application + Presentation (API + Web/Inertia) + Eventos + **alertas persistidas** + **login + roles reales** + Vue + Dockerfile + Dockerfile.dev (Postgres) + CI + seeder + README.
- 🔜 Futuro: SSR de Inertia, portear los feature tests a Postgres, más tests de edge cases.
