<?php

namespace App\Http\Middleware;

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
        return array_merge(parent::share($request), [
            //
        ]);
    }
}
