<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Supermercado\Application\Stock\ListarStock;
use Supermercado\Application\Ventas\CobrarProductos;
use Supermercado\Application\Ventas\CobrarRequest;
use Supermercado\Application\Ventas\ItemRequest;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Ventas\MetodoDePago;
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

        $sale = $this->checkout->execute(new CobrarRequest(
            saleId: Str::uuid()->toString(),
            cashierId: 'storefront',
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
}
