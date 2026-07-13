<?php

namespace App\Http\Middleware;

use App\Access\Perfil;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gatea los endpoints de la API REST por rol del usuario autenticado.
 *
 * A diferencia de {@see RequierePerfil} (que redirige, para el frontend web),
 * este middleware responde 401/403 en JSON — apropiado para clientes de API.
 * Se usa como `rol:cajero`, `rol:depositista`, `rol:repositor` sobre rutas
 * protegidas con `auth:sanctum`.
 */
final class RequiereRol
{
    /**
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @param  string  ...$roles  Valores del enum Perfil permitidos (cajero, depositista, repositor).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $perfil = $user->perfil();

        foreach ($roles as $rol) {
            if ($perfil === Perfil::from($rol)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Esta acción requiere el rol ' . implode(' o ', $roles) . '.'], 403);
    }
}
