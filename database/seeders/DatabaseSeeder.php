<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Ventas\EstadoDeVenta;
use Supermercado\Domain\Ventas\LineaDeVenta;
use Supermercado\Domain\Ventas\MetodoDePago;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\VentaRepository;
use Supermercado\Infrastructure\Persistence\OfertaModel;

/**
 * Seeds a rich demo dataset (idempotent) so the storefront looks like a real
 * supermarket and the CLI / admin panel have meaningful data to operate on.
 *
 * ~30 products across 7 categories, varied stock levels (including low-stock
 * and out-of-stock to exercise alerts), distributed offers, and a demo
 * customer account for testing the storefront checkout flow.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products   = app(ProductoRepository::class);
        $shelves    = app(GondolaRepository::class);
        $warehouses = app(DepositoRepository::class);

        // [id, nombre, precio(cents), gondola, deposito, % oferta]
        // Categorías: Lácteos, Panadería, Bebidas, Almacén, Frescos, Limpieza, Cuidado personal
        $demo = [
            // ── Lácteos ──────────────────────────────────────────────
            ['p-1',  'Leche 1L',          150,  45,  500, 10],
            ['p-4',  'Queso Cremoso 500g', 1200, 25, 200,  0],
            ['p-5',  'Yogurt Bebible 1L',  350,  30, 180,  5],
            ['p-6',  'Manteca 200g',       480,  18, 120,  0],
            ['p-7',  'Huevos (docena)',    850,  40, 300,  0],

            // ── Panadería ────────────────────────────────────────────
            ['p-2',  'Pan',               300,   8,  160,  0],  // gondola baja
            ['p-8',  'Galletas Dulces',   420,  55, 400, 15],
            ['p-9',  'Facturas (6u)',     650,  20,  90,  0],
            ['p-10', 'Pan Lactal',        520,  35, 250,  0],

            // ── Bebidas ──────────────────────────────────────────────
            ['p-11', 'Agua Mineral 2L',   280,  80, 600,  0],
            ['p-12', 'Gaseosa Cola 2.25L',580,  60, 800, 10],
            ['p-13', 'Jugo de Naranja 1L',450,  15, 140,  0],  // gondola baja
            ['p-14', 'Cerveza 1L',        720,  50, 700,  0],
            ['p-15', 'Vino Tinto 750ml',  1450, 22, 200, 20],

            // ── Almacén ──────────────────────────────────────────────
            ['p-3',  'Café 500g',        2500,  60, 800, 15],
            ['p-16', 'Arroz 500g',        380,  70, 500,  0],
            ['p-17', 'Fideos 500g',       320,  65, 450,  5],
            ['p-18', 'Aceite de Girasol 900ml', 980, 40, 300, 10],
            ['p-19', 'Azúcar 1kg',        420,  50, 350,  0],
            ['p-20', 'Sal Fina 500g',     150,  85, 600,  0],
            ['p-21', 'Té en Saquitos 50u',350,  25, 150,  0],

            // ── Frescos ──────────────────────────────────────────────
            ['p-22', 'Manzana (kg)',      580,  35, 250,  0],
            ['p-23', 'Banana (kg)',       520,  28, 200,  0],
            ['p-24', 'Tomate (kg)',       680,   0, 180,  0],  // sin stock en góndola
            ['p-25', 'Lechuga',           280,  12,  80,  0],  // gondola muy baja
            ['p-26', 'Carne Picada (kg)', 2400, 18, 150,  0],
            ['p-27', 'Pollo Entero (kg)', 1450, 22, 200,  0],

            // ── Limpieza ─────────────────────────────────────────────
            ['p-28', 'Lavandina 1L',      350,  45, 350,  0],
            ['p-29', 'Detergente 500ml',  520,  30, 200, 10],
            ['p-30', 'Papel Higiénico (12u)', 890, 50, 400,  0],

            // ── Cuidado personal ─────────────────────────────────────
            ['p-31', 'Shampoo 400ml',     850,  20, 150,  0],
            ['p-32', 'Pasta Dental 75ml', 420,  38, 250,  5],
        ];

        $now = now();

        foreach ($demo as [$id, $name, $price, $shelfQty, $warehouseQty, $offerPercent]) {
            $products->save(new Producto($id, $name, new Dinero($price, 'ARS')));
            $shelves->save(new Gondola($id, $shelfQty));
            $warehouses->save(new Deposito($id, $warehouseQty));

            if ($offerPercent > 0) {
                OfertaModel::firstOrCreate(
                    ['product_id' => $id, 'percent' => $offerPercent],
                    ['valid_from' => $now->copy()->subDay(), 'valid_to' => $now->copy()->addDays(30)],
                );
            }
        }

        // Usuarios demo para el login web con roles (uno por perfil).
        // Contraseña: 'password' (hasheada por el cast del modelo User).
        User::firstOrCreate(['email' => 'cajero@supermercado.test'],        ['name' => 'Cajero Demo',              'password' => 'password', 'rol' => 'cajero']);
        User::firstOrCreate(['email' => 'depositista@supermercado.test'],   ['name' => 'Empleado del depósito',    'password' => 'password', 'rol' => 'depositista']);
        User::firstOrCreate(['email' => 'repositor@supermercado.test'],     ['name' => 'Repositor Demo',           'password' => 'password', 'rol' => 'repositor']);

        // Cliente demo para testing del storefront.
        User::firstOrCreate(['email' => 'cliente@supermercado.test'], ['name' => 'Cliente Demo', 'password' => 'password', 'rol' => 'cliente']);

        // ~30 ventas confirmadas distribuidas en los últimos 7 días para que
        // los reportes y el tablero del cajero tengan datos que graficar.
        $this->seedVentasDemo($products, app(VentaRepository::class));
    }

    /**
     * Genera ventas confirmadas determinísticas (mt_srand fija) repartidas
     * en los últimos 7 días. Usa IDs fijos (demo-venta-N) para que el seed
     * sea idempotente: re-ejecutar el seeder sobrescribe, no duplica.
     */
    private function seedVentasDemo(ProductoRepository $productos, VentaRepository $ventas): void
    {
        $todos = $productos->all();
        if ($todos === []) {
            return;
        }

        $metodos  = MetodoDePago::cases();
        $nombres  = ['Cliente Demo', 'María González', 'Juan Pérez', 'Ana Martínez', 'Carlos Ruiz', 'Sofía Romero'];
        $cajeros  = ['cajero@supermercado.test', 'demo', 'storefront', 'cliente:1'];
        $ahora    = new \DateTimeImmutable();

        \mt_srand(42); // Determinístico — mismos datos en cada seed.

        for ($i = 1; $i <= 30; $i++) {
            $diasAtras = \mt_rand(0, 6);
            $hora      = \mt_rand(8, 20);
            $minuto    = \mt_rand(0, 59);
            $fecha     = $ahora->modify("-{$diasAtras} days")->setTime($hora, $minuto, 0);

            $cantLineas  = \mt_rand(1, 4);
            $lineas      = [];
            $usados      = [];

            for ($j = 0; $j < $cantLineas; $j++) {
                $idx = \mt_rand(0, count($todos) - 1);
                if (\in_array($idx, $usados, true)) {
                    continue;
                }
                $usados[] = $idx;

                $p = $todos[$idx];
                $lineas[] = new LineaDeVenta(
                    $p->id(),
                    $p->name(),
                    \mt_rand(1, 5),
                    $p->price(),
                );
            }

            if ($lineas === []) {
                continue;
            }

            $venta = Venta::reconstitute(
                id: "demo-venta-{$i}",
                cashierId: $cajeros[\mt_rand(0, count($cajeros) - 1)],
                customerName: $nombres[\mt_rand(0, count($nombres) - 1)],
                metodoDePago: $metodos[\mt_rand(0, count($metodos) - 1)],
                createdAt: $fecha,
                status: EstadoDeVenta::Confirmada,
                lines: $lineas,
            );

            $ventas->save($venta);
        }
    }
}
