<?php

namespace App\Http\Controllers\Web;

use App\Facades\Perfil;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Supermercado\Application\Stock\ListarAlertas;
use Supermercado\Application\Stock\ListarMovimientos;
use Supermercado\Application\Stock\ListarStock;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Ventas\MetodoDePago;

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
