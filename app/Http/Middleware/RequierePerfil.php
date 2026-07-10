<?php

namespace App\Http\Middleware;

use App\Facades\Perfil;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gatea las vistas del frontend por perfil:
 *  - sin perfil seleccionado → redirige al selector (/iniciar);
 *  - ruta no permitida para el perfil actual → redirige al home de ese perfil.
 *
 * La raíz ('inicio') siempre se permite: es un mero redirect al home del perfil.
 */
class RequierePerfil
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Perfil::tiene()) {
            return redirect()->route('iniciar');
        }

        $perfil = Perfil::actual();
        \assert($perfil !== null);

        $ruta = $request->route()?->getName();
        $permitidas = array_map(static fn (array $p) => $p['ruta'], $perfil->paginas());

        if ($ruta !== 'inicio' && ! in_array($ruta, $permitidas, true)) {
            return redirect()->route($permitidas[0]);
        }

        return $next($request);
    }
}
