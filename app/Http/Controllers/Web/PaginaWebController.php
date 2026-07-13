<?php

namespace App\Http\Controllers\Web;

use App\Facades\Perfil;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Supermercado\Application\Auditoria\ListarEventos;
use Supermercado\Application\Reportes\ObtenerReporteMovimientos;
use Supermercado\Application\Reportes\ObtenerReporteVentas;
use Supermercado\Application\Stock\ListarAlertas;
use Supermercado\Application\Stock\ListarMovimientos;
use Supermercado\Application\Stock\ListarStock;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Ventas\MetodoDePago;
use Supermercado\Application\Tableros\ObtenerTableroCajero;
use Supermercado\Application\Tableros\ObtenerTableroDepositista;
use Supermercado\Application\Tableros\ObtenerTableroRepositor;
use Supermercado\Infrastructure\Persistence\OfertaModel;

/**
 * Sirve las páginas Vue (Inertia) del frontend del supermercado.
 * Las listas se pasan como props; las mutaciones (checkout) las hace la vista
 * contra la API REST (/api/*), reutilizando el backend existente.
 */
final class PaginaWebController extends Controller
{
    public function stock(ListarStock $listar): Response
    {
        return Inertia::render('Stock', [
            'items' => $listar->execute(),
        ]);
    }

    public function cobrar(ProductoRepository $productos): Response
    {
        return Inertia::render('Cobrar', [
            'productos' => array_map(
                static fn ($p) => [
                    'id' => $p->id(),
                    'nombre' => $p->name(),
                    'precio' => $p->price()->amount() / 100,
                    'moneda' => $p->price()->currency(),
                ],
                $productos->all(),
            ),
            'metodosDePago' => array_map(
                static fn (MetodoDePago $m) => ['value' => $m->value, 'label' => $m->value],
                MetodoDePago::cases(),
            ),
        ]);
    }

    public function movimientos(ListarMovimientos $listar, ListarStock $stock): Response
    {
        return Inertia::render('Movimientos', [
            'movimientos' => $listar->execute(),
            'stockDeposito' => $stock->execute(),
        ]);
    }
    public function alertas(ListarAlertas $listar): Response
    {
        return Inertia::render('Alertas', [
            'alertas' => $listar->execute(),
        ]);
    }
    public function cierre(): Response
    {
        return Inertia::render('Cierre');
    }

    public function tablero(
        ObtenerTableroCajero $cajero,
        ObtenerTableroDepositista $depositista,
        ObtenerTableroRepositor $repositor,
    ): Response {
        $datos = match (Perfil::actual()->value) {
            'cajero' => ['tipo' => 'cajero', 'datos' => (array) $cajero->execute()],
            'depositista' => ['tipo' => 'depositista', 'datos' => (array) $depositista->execute()],
            'repositor' => ['tipo' => 'repositor', 'datos' => (array) $repositor->execute()],
        };

        return Inertia::render('Tablero', $datos);
    }

    public function catalogo(ProductoRepository $productos): Response
    {
        $ofertas = OfertaModel::all()->map(static fn ($o) => [
            'id' => $o->id,
            'productoId' => $o->product_id,
            'porcentaje' => $o->percent,
            'validoDesde' => $o->valid_from->format('Y-m-d H:i:s'),
            'validoHasta' => $o->valid_to->format('Y-m-d H:i:s'),
        ]);

        return Inertia::render('Catalogo', [
            'productos' => array_map(static fn ($p) => [
                'id' => $p->id(),
                'nombre' => $p->name(),
                'precio' => $p->price()->amount() / 100,
                'moneda' => $p->price()->currency(),
            ], $productos->all()),
            'ofertas' => $ofertas,
        ]);
    }

    public function auditoria(ListarEventos $eventos): Response
    {
        return Inertia::render('Auditoria', [
            'eventos' => array_map(static fn ($e) => (array) $e, $eventos->execute()),
        ]);
    }

    public function reportes(
        ObtenerReporteVentas $ventas,
        ObtenerReporteMovimientos $movimientos,
    ): Response {
        return Inertia::render('Reportes', [
            'ventas' => (array) $ventas->execute(),
            'movimientos' => (array) $movimientos->execute(),
        ]);
    }

    public function login(): Response
    {
        return Inertia::render('Perfiles/Login');
    }

    public function autenticar(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credenciales, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route(Perfil::actual()->paginas()[0]['ruta']);
    }

    public function cerrarSesion(Request $request)
    {
        Perfil::limpiar();

        return redirect()->route('login');
    }
}
