# Supermarket Stock — Laravel + DDD (hexagonal)

[![tests](https://github.com/Fabrizio-Alvarez/Ejercicio-Arquitectura/actions/workflows/ci.yml/badge.svg)](https://github.com/Fabrizio-Alvarez/Ejercicio-Arquitectura/actions/workflows/ci.yml)

A supermarket stock-management backend (point-of-sale, cash close, stock
replenishment, low-stock alerts) built in **Laravel 13 + PHP 8.4** with a
strict **Domain-Driven Design / hexagonal** architecture.

The requirements come from a real spec (`docs/specs/`). The interesting part is
**not** the feature set — it is the architecture: the **domain layer is pure
PHP, tested in isolation with no framework and no database.**

> The headline of this repo: the domain unit tests boot **zero** Laravel. That
> is what proves the hexagonal boundary is real, not decoration.

---

## Architecture

```
flowchart TD
  subgraph Presentation["Presentation (Laravel)"]
    HTTP["REST controllers (api.php)"]
    CLI["Artisan command — repositor"]
  end
  subgraph Application["Application (use cases)"]
    UC["CobrarProductos · ObtenerCierreDeCaja<br/>ListarStock · RegistrarReposicion"]
  end
  subgraph Domain["Domain — PURE PHP, no framework"]
    AGG["Aggregates: Sale, Product, Offer, Shelf, Warehouse"]
    VO["Value objects: Money, Offer"]
    SVC["Domain services: Pricer, ReplenishmentPolicy"]
  end
  subgraph Infrastructure["Infrastructure (Laravel/Eloquent)"]
    PORT["Repository ports (interfaces)"]
    ADP["Eloquent adapters"]
  end
  HTTP --> UC
  CLI --> UC
  UC --> AGG
  UC --> SVC
  UC --> PORT
  ADP -.->|"implements port"| PORT
  ADP --> ELO["Eloquent models + migrations"]
```

- **Domain** knows nothing about Laravel, the database, or HTTP. It holds the
  invariants: a `Money` cannot mix currencies; a `Sale` cannot be confirmed
  empty nor edited after confirmation; the replenishment rule (<30 → fill to 50,
  alert if warehouse <150) lives in a pure service.
- **Application** orchestrates use cases against **repository ports**
  (interfaces defined in the domain).
- **Infrastructure** provides the Eloquent adapters that translate between
  database rows and domain objects.

Dependency direction is always **inward**: the domain depends on nothing.

---

## Use cases (the 6 from the spec)

| # | Use case | Where |
|---|----------|-------|
| 1 | Price products applying active offers | `Pricer` + `CobrarProductos` |
| 2 | Register a sale | `Sale` aggregate + `CobrarProductos` |
| 3 | Obtain the cash close (per cashier/day) | `CashClose` + `ObtenerCierreDeCaja` |
| 4 | List stock | `ListarStock` |
| 5 | Register replenishment | `RegistrarReposicion` + `ReplenishmentPolicy` |
| 6 | Emit low-stock alert | `StockAlert` (emitted by #5 when warehouse < 150) |

---

## API

| Method | Route | Body / Query |
|--------|-------|--------------|
| `POST` | `/api/checkout` | `{saleId, cashierId, customerName, items:[{productId,quantity}]}` → 201, sale + total |
| `GET`  | `/api/stock` | → stock view per product (shelf + warehouse + low flags) |
| `POST` | `/api/replenish/{productId}` | → replenishment outcome + alert |
| `GET`  | `/api/cash-close` | `?cashierId=&date=` → daily cash close report |

Repositor CLI: `php artisan stock:replenish {productId}`

---

## Run

```bash
# Local dev via Docker (mirrors how the project was built):
docker compose run --rm app composer install
docker compose run --rm app php artisan migrate
docker compose run --rm app php artisan serve --host=0.0.0.0 --port=8000
```

Storage is **SQLite** (`:memory:` in tests, file in dev). A production image is
provided via `Dockerfile` (single container, `php artisan serve`, reads `PORT`).

---

## Test

```bash
docker compose run --rm app php vendor/bin/pest
```

Test pyramid:
- **Unit (pure PHP domain)** — `tests/Unit/Domain/**`: Money, Offer, Sale
  aggregate (state machine, currency invariant), Pricer, ReplenishmentPolicy.
  No Laravel, no DB.
- **Feature (persistence + use cases + HTTP)** — `tests/Feature/**`: repository
  adapters against a real SQLite DB, the use cases, and the REST API.

---

## Case study — decisions worth defending

- **Why hexagonal / pure domain.** The domain is where the business rules live
  and change. Keeping it framework-free means the rules are unit-testable at the
  speed of thought, with zero bootstrap — and could be ported to another
  framework without rewriting the logic.
- **`Money` value object (integer cents).** Amounts are stored and operated on
  as integer cents, never floats — the classic `0.1 + 0.2 ≠ 0.3` bug is
  structurally impossible. Cross-currency operations throw.
- **Price frozen at sale time.** A `SaleLine` snapshots the (possibly
  discounted) unit price, so a sale's total is immutable even if offers change
  later — sales are auditable.
- **Best-active-offer pricing.** The `Pricer` picks the highest active offer
  covering a product; expired/future offers are ignored.
- **Aggregate invariants.** `Sale` enforces its own rules (no edits after
  confirmation, single currency, no empty confirmation). `Sale::reconstitute`
  is the only path that bypasses them — for loading trusted persisted data.
- **Replenishment rule as a pure decision.** `ReplenishmentPolicy::decide` is a
  pure function of (shelf, warehouse) → (move, alert). The application layer
  applies and persists it. The rule is exhaustively unit-tested, including the
  `< 150` alert boundary.

---

## Spec

Original requirements: [`docs/specs/Especificaciones funcionales.md`](docs/specs/Especificaciones%20funcionales.md)
and [`docs/specs/Especificaciones no funcionales.md`](docs/specs/Especificaciones%20no%20funcionales.md).

---

## Deploy

Single container via `Dockerfile` (reads `PORT`, runs migrations + seeds demo data on start). On [Railway](https://railway.app):

1. **New Project → Deploy from GitHub repo** → this repo.
2. Railway detects the `Dockerfile`, builds and deploys (it injects `PORT`).
3. Settings → Networking → **Generate Domain** → public URL.
4. Try `<url>/up` (health) and `<url>/api/stock`.
