<?php

namespace App\Access;

use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;

/**
 * Mantiene el perfil actual del usuario.
 *
 * Con auth real: el perfil se deriva del usuario autenticado (su campo `rol`
 * mapea al enum Perfil), no de un selector manual. Se resuelve vía el Facade
 * {@see \App\Facades\Perfil} (binding 'sesion.de.perfil'), de modo que
 * controladores y middleware acceden con Perfil::actual() sin tocar Auth.
 */
class SesionDePerfil
{
    public function __construct(private readonly Store $session)
    {
    }

    public function actual(): ?Perfil
    {
        $user = Auth::user();

        return $user !== null ? $user->perfil() : null;
    }

    public function tiene(): bool
    {
        return $this->actual() !== null;
    }

    /** Cierra la sesión del usuario autenticado. */
    public function limpiar(): void
    {
        Auth::logout();
        $this->session->invalidate();
        $this->session->regenerateToken();
    }

    /**
     * Todos los perfiles disponibles (para info de UI).
     *
     * @return array<int, Perfil>
     */
    public function todos(): array
    {
        return Perfil::cases();
    }
}
