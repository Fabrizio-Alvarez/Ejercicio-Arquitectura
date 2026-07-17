<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gatea las rutas de cuenta de cliente en /tienda/cuenta/*.
 * Requiere un usuario autenticado con rol 'cliente'.
 */
class RequiereCliente
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->rol !== 'cliente') {
            return redirect()->route('tienda.login');
        }

        return $next($request);
    }
}
