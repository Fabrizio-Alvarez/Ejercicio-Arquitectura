<?php

namespace App\Http\Middleware;

use App\Facades\Perfil;
use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Middleware de Inertia: inyecta el objeto "page" en las respuestas y permite
 * compartir props globales con todas las vistas.
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * Props compartidas por defecto con todas las páginas Inertia.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $perfil = Perfil::tiene() ? Perfil::actual() : null;
        $usuario = \Illuminate\Support\Facades\Auth::user();

        return array_merge(parent::share($request), [
            'perfil' => $perfil === null ? null : [
                'value' => $perfil->value,
                'etiqueta' => $perfil->etiqueta(),
                'paginas' => $perfil->paginas(),
            ],
            'usuario' => $usuario !== null ? ['nombre' => $usuario->name, 'email' => $usuario->email] : null,
        ]);
    }
}
