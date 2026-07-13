<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Login de API: valida email + password y emite un token de Sanctum.
 *
 * El token hereda el rol del usuario (vía User::perfil()), de modo que los
 * endpoints protegidos con el middleware `rol` lo autorizan sin abilities.
 * El token se devuelve una sola vez (plaintext) — el cliente debe guardarlo.
 */
final class TokenController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credenciales)) {
            return response()->json(['message' => 'Las credenciales no coinciden.'], 422);
        }

        $user = Auth::user();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'rol' => $user->perfil()->value,
        ]);
    }
}
