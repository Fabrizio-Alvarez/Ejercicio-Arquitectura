<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

/**
 * Provee lectura/escritura de una colección de filas sobre un archivo JSON en
 * disco. Lo usan los adapters Json*Repository para cumplir el spec no funcional:
 * "el sistema debe utilizar archivos de texto plano (JSON) almacenados en disco
 * como origen de datos".
 *
 * Cada adapter declara su nombre de archivo y su hidratación (dominio ↔ fila).
 * El directorio base es configurable (config('supermercado.json_dir')), por
 * defecto storage/app/supermercado — útil para apuntar los tests a un tmp dir.
 */
trait AlmacenaJson
{
    /** @return array<int, array<string, mixed>> */
    private function leer(): array
    {
        $ruta = $this->ruta();

        if (! file_exists($ruta)) {
            return [];
        }

        $contenido = file_get_contents($ruta);
        $datos = json_decode((string) $contenido, true);

        return is_array($datos) ? $datos : [];
    }

    /** @param array<int, array<string, mixed>> $filas */
    private function escribir(array $filas): void
    {
        $ruta = $this->ruta();

        if (! is_dir(dirname($ruta))) {
            @mkdir(dirname($ruta), 0777, true);
        }

        file_put_contents(
            $ruta,
            json_encode(array_values($filas), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        );
    }

    private function ruta(): string
    {
        $directorio = config('supermercado.json_dir', storage_path('app/supermercado'));

        return rtrim((string) $directorio, '/\\').DIRECTORY_SEPARATOR.$this->nombreArchivo();
    }

    /** Nombre del archivo JSON, p.ej. 'productos.json'. */
    abstract protected function nombreArchivo(): string;
}
