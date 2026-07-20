<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use Inertia\Inertia;
use Inertia\Response;
use Supermercado\Application\Stock\ListarStock;
use Supermercado\Application\Ventas\CobrarProductos;
use Supermercado\Application\Ventas\CobrarRequest;
use Supermercado\Application\Ventas\ItemRequest;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Ventas\MetodoDePago;
use Supermercado\Domain\Ventas\Venta;

use Supermercado\Domain\Ventas\VentaRepository;
use Supermercado\Infrastructure\Persistence\OfertaModel;

/**
 * Storefront público: catálogo, detalle de producto y checkout.
 * No requiere autenticación de staff — es la cara visible del supermercado.
 */
final class TiendaController extends Controller
{
    public function __construct(
        private readonly ProductoRepository $productos,
        private readonly GondolaRepository $gondolas,
        private readonly CobrarProductos $checkout,
        private readonly VentaRepository $ventas,
    ) {}

    /** Landing page con productos destacados. */
    public function inicio(ListarStock $listar): Response
    {
        $stockMap = $this->stockMap($listar->execute());
        $productos = $this->mapProductos($this->productos->all(), $stockMap);

        return Inertia::render('Tienda/Inicio', [
            'destacados' => array_slice($productos, 0, 6),
            'totalProductos' => count($productos),
        ]);
    }

    /** Catálogo completo con búsqueda, ordenamiento. */
    public function catalogo(Request $request, ListarStock $listar): Response
    {
        $stockMap = $this->stockMap($listar->execute());
        $productos = $this->mapProductos($this->productos->all(), $stockMap);

        // Filtro por búsqueda (nombre o id)
        $q = trim($request->query('q', ''));
        if ($q !== '') {
            $qLower = strtolower($q);
            $productos = array_values(array_filter(
                $productos,
                fn ($p) => str_contains(strtolower($p['nombre']), $qLower)
                    || str_contains(strtolower($p['id']), $qLower),
            ));
        }

        // Ordenamiento
        $sort = $request->query('sort', 'nombre');
        usort($productos, function ($a, $b) use ($sort) {
            return match ($sort) {
                'precio-asc'  => $a['precio'] <=> $b['precio'],
                'precio-desc' => $b['precio'] <=> $a['precio'],
                default       => $a['nombre'] <=> $b['nombre'],
            };
        });

        return Inertia::render('Tienda/Catalogo', [
            'productos' => $productos,
            'filtros' => [
                'q' => $q,
                'sort' => $sort,
            ],
        ]);
    }

    /** Detalle de un producto. */
    public function producto(string $id, ListarStock $listar): Response
    {
        $producto = $this->productos->find($id);

        if ($producto === null) {
            abort(404);
        }

        $stockMap = $this->stockMap($listar->execute());
        $stock = $stockMap[$id] ?? ['gondola' => 0, 'deposito' => 0];

        // Ofertas activas para este producto
        $ofertas = OfertaModel::where('producto_id', $id)
            ->where('valido_desde', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valido_hasta')->orWhere('valido_hasta', '>=', now());
            })
            ->get();

        return Inertia::render('Tienda/Producto', [
            'producto' => [
                'id' => $producto->id(),
                'nombre' => $producto->name(),
                'precio' => $producto->price()->amount() / 100,
                'moneda' => $producto->price()->currency(),
                'gondola' => $stock['gondola'],
                'deposito' => $stock['deposito'],
                'disponible' => ($stock['gondola'] + $stock['deposito']) > 0,
            ],
            'ofertas' => $ofertas->map(fn ($o) => [
                'porcentaje' => $o->porcentaje,
                'validoDesde' => $o->valido_desde?->format('d/m/Y'),
                'validoHasta' => $o->valido_hasta?->format('d/m/Y'),
            ]),
        ]);
    }

    /** Página de checkout — muestra resumen del carrito y formulario. */
    public function checkout(): Response
    {
        return Inertia::render('Tienda/Checkout', [
            'metodosDePago' => array_map(
                fn (MetodoDePago $m) => ['value' => $m->value, 'label' => $m->value],
                MetodoDePago::cases(),
            ),
        ]);
    }

    /** Procesa el checkout: llama al caso de uso CobrarProductos. */
    public function confirmar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customerName' => ['required', 'string', 'max:120'],
            'paymentMethod' => ['required', 'string', 'in:efectivo,tarjeta_credito,tarjeta_debito,transferencia,qr'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.productId' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.productName' => ['required', 'string'],
            'items.*.unitPrice' => ['required', 'numeric'],
        ]);

        $items = array_map(
            fn (array $item) => new ItemRequest($item['productId'], $item['quantity']),
            $data['items'],
        );

        // Si hay un cliente logueado, asociar la venta a su cuenta.
        $user = $request->user();
        $cashierId = ($user !== null && $user->rol === 'cliente')
            ? 'cliente:' . $user->id
            : 'storefront';

        $sale = $this->checkout->execute(new CobrarRequest(
            saleId: Str::uuid()->toString(),
            cashierId: $cashierId,
            customerName: $data['customerName'],
            items: $items,
            metodoDePago: MetodoDePago::from($data['paymentMethod']),
        ));

        return redirect()->route('tienda.confirmacion', ['ventaId' => $sale->id()])
            ->with('venta', [
                'id' => $sale->id(),
                'total' => $sale->total()->amount() / 100,
                'moneda' => $sale->total()->currency(),
                'customerName' => $sale->customerName(),
                'paymentMethod' => $sale->metodoDePago()->value,
                'items' => array_map(fn ($line) => [
                    'productName' => $line->productName(),
                    'quantity' => $line->quantity(),
                    'unitPrice' => $line->unitPrice()->amount() / 100,
                ], $sale->lines()),
            ]);
    }

    /** Página de confirmación post-checkout. */
    public function confirmacion(Request $request, string $ventaId): Response|RedirectResponse
    {
        $venta = session('venta');

        if (!$venta) {
            return redirect()->route('tienda.catalogo');
        }

        return Inertia::render('Tienda/Confirmacion', [
            'venta' => $venta,
        ]);
    }

    /**
     * @return array<string, array{gondola: int, deposito: int}>
     */
    private function stockMap(array $stockViews): array
    {
        $map = [];
        foreach ($stockViews as $view) {
            $map[$view->productId] = [
                'gondola' => $view->shelfQuantity,
                'deposito' => $view->warehouseQuantity,
            ];
        }
        return $map;
    }

    private function mapProductos(array $productos, array $stockMap): array
    {
        return array_map(fn ($p) => [
            'id' => $p->id(),
            'nombre' => $p->name(),
            'precio' => $p->price()->amount() / 100,
            'moneda' => $p->price()->currency(),
            'gondola' => $stockMap[$p->id()]['gondola'] ?? 0,
            'disponible' => ($stockMap[$p->id()]['gondola'] ?? 0) > 0,
        ], $productos);
    }

    // ─── Cuenta de cliente ───────────────────────────────────────────

    /** Página de registro de cliente. */
    public function registro(): Response
    {
        return Inertia::render('Tienda/Cuenta/Registro');
    }

    /** Procesa el registro de un nuevo cliente. */
    public function registrar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'rol' => 'cliente',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('tienda.catalogo');
    }

    /** Página de login de cliente. */
    public function login(): Response
    {
        return Inertia::render('Tienda/Cuenta/Login');
    }

    /** Procesa el login de cliente. */
    public function autenticar(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credenciales, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        // Staff no puede entrar por la tienda — redirige al panel admin.
        $user = Auth::user();
        if ($user->rol !== 'cliente') {
            Auth::logout();
            return back()->withErrors(['email' => 'Esta cuenta es del personal. Usá el login del panel.']);
        }

        return redirect()->intended(route('tienda.catalogo'));
    }

    /** Cierra sesión del cliente. */
    public function cerrarSesion(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tienda.inicio');
    }

    /** Historial de pedidos del cliente logueado. */
    public function pedidos(Request $request): Response
    {
        $user = $request->user();
        $prefix = 'cliente:' . $user->id;

        $ventas = array_filter(
            $this->ventas->all(),
            fn (Venta $v) => str_starts_with($v->cashierId(), $prefix),
        );

        usort($ventas, fn (Venta $a, Venta $b) => $b->createdAt() <=> $a->createdAt());

        $pedidos = array_map(fn (Venta $v) => [
            'id' => $v->id(),
            'fecha' => $v->createdAt()->format('d/m/Y H:i'),
            'total' => $v->total()->amount() / 100,
            'moneda' => $v->total()->currency(),
            'estado' => $v->status()->value,
            'metodoDePago' => $v->metodoDePago()->value,
            'itemsCount' => count($v->lines()),
        ], array_values($ventas));

        return Inertia::render('Tienda/Cuenta/Pedidos', ['pedidos' => $pedidos]);
    }

    /** Detalle de un pedido del cliente logueado. */
    public function pedido(Request $request, string $id): Response|RedirectResponse
    {
        $user = $request->user();
        $venta = $this->ventas->find($id);

        if ($venta === null || !str_starts_with($venta->cashierId(), 'cliente:' . $user->id)) {
            return redirect()->route('tienda.cuenta.pedidos');
        }

        return Inertia::render('Tienda/Cuenta/Pedido', [
            'pedido' => [
                'id' => $venta->id(),
                'fecha' => $venta->createdAt()->format('d/m/Y H:i'),
                'total' => $venta->total()->amount() / 100,
                'moneda' => $venta->total()->currency(),
                'estado' => $venta->status()->value,
                'metodoDePago' => $venta->metodoDePago()->value,
                'customerName' => $venta->customerName(),
                'items' => array_map(fn ($line) => [
                    'productName' => $line->productName(),
                    'quantity' => $line->quantity(),
                    'unitPrice' => $line->unitPrice()->amount() / 100,
                ], $venta->lines()),
            ],
        ]);
    }
}
