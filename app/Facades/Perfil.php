<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Fachada de acceso al perfil del usuario actual (auth real con roles).
 *
 * Delega en {@see \App\Access\SesionDePerfil} (binding 'sesion.de.perfil'),
 * que deriva el perfil del usuario autenticado. Mantiene controladores y
 * middleware libres de lógica de Auth.
 *
 * @method static \App\Access\Perfil|null actual()   Perfil del usuario autenticado o null.
 * @method static void                    limpiar()   Cierra la sesión (logout).
 * @method static bool                    tiene()     ¿Hay usuario autenticado?
 * @method static array<int, \App\Access\Perfil> todos()  Todos los perfiles (info de UI).
 */
class Perfil extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sesion.de.perfil';
    }
}
