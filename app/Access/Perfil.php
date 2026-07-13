<?php

namespace App\Access;

/**
 * Perfil de usuario del frontend del supermercado.
 *
 * Cada perfil ve una vista distinta (definida por paginas()). Es el único
 * "control de acceso" de la app por ahora: un selector sin login.
 *
 * PHP puro, sin Laravel — para que la lógica de qué ve cada perfil sea
 * reutilizable y testeable aislada.
 */
enum Perfil: string
{
    case Cajero = 'cajero';
    case Depositista = 'depositista';
    case Repositor = 'repositor';

    /** Etiqueta legible para la UI. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::Cajero => 'Cajero',
            self::Depositista => 'Empleado del depósito',
            self::Repositor => 'Repositor',
        };
    }

    /** Descripción corta para el selector de perfiles. */
    public function descripcion(): string
    {
        return match ($this) {
            self::Cajero => 'Registra ventas en el punto de venta.',
            self::Depositista => 'Consulta los movimientos de stock del depósito.',
            self::Repositor => 'Monitorea el stock de góndola y repone cuando baja.',
        };
    }

    /**
     * Páginas que este perfil puede ver.
     *
     * @return array<int, array{ruta: string, etiqueta: string}>
     */
    public function paginas(): array
    {
        return match ($this) {
            self::Cajero      => [['ruta' => 'cobrar', 'etiqueta' => 'Registrar venta'], ['ruta' => 'cierre', 'etiqueta' => 'Cierre de caja']],
            self::Depositista => [['ruta' => 'movimientos', 'etiqueta' => 'Movimientos'], ['ruta' => 'alertas', 'etiqueta' => 'Alertas']],
            self::Repositor   => [['ruta' => 'stock', 'etiqueta' => 'Stock']],
        };
    }
}
