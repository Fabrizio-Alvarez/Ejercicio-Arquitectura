<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Fachada de acceso al perfil de usuario actual (selector de perfiles, sin login).
 *
 * Delega en {@see \App\Access\SesionDePerfil} (binding 'sesion.de.perfil'),
 * manteniendo controladores y middleware libres de lógica de sesión.
 *
 * @method static \App\Access\Perfil|null actual()                     Perfil seleccionado o null.
 * @method static void                    establecerPorValor(string $v) Persiste el perfil por su valor.
 * @method static void                    limpiar()                     Borra el perfil de la sesión.
 * @method static bool                    tiene()                       ¿Hay perfil seleccionado?
 * @method static array<int, \App\Access\Perfil> todos()               Todos los perfiles (selector).
 */
class Perfil extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sesion.de.perfil';
    }
}
