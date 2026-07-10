<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
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

    public function movimientos(ListarMovimientos $listar): Response
    {
        return Inertia::render('Movimientos', [
            'movimientos' => $listar->execute(),
        ]);
    }
}
