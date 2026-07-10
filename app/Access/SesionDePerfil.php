<?php

namespace App\Access;

use Illuminate\Session\Store;

/**
 * Mantiene el perfil actual en sesión.
 *
 * Se resuelve vía el Facade {@see \App\Facades\Perfil} (binding
 * 'sesion.de.perfil'), de modo que controladores y middleware acceden con
 * Perfil::actual() / Perfil::establecerPorValor() sin tocar la sesión a mano.
 */
class SesionDePerfil
{
    private const KEY = 'perfil';

    public function __construct(private readonly Store $session)
    {
    }

    public function actual(): ?Perfil
    {
        $valor = $this->session->get(self::KEY);

        return $valor !== null ? Perfil::tryFrom($valor) : null;
    }

    public function establecerPorValor(string $valor): void
    {
        $this->session->put(self::KEY, Perfil::from($valor)->value);
    }

    public function limpiar(): void
    {
        $this->session->forget(self::KEY);
    }

    public function tiene(): bool
    {
        return $this->actual() !== null;
    }

    /**
     * Todos los perfiles disponibles (para pintar el selector).
     *
     * @return array<int, Perfil>
     */
    public function todos(): array
    {
        return Perfil::cases();
    }
}
