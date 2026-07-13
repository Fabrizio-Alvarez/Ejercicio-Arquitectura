<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Origen de datos del supermercado
    |--------------------------------------------------------------------------
    |
    | El dominio define los puertos de repositorio; la infraestructura provee
    | dos adapters: Eloquent (SQLite/Postgres) y Json (archivos de texto plano
    | en disco, como pide el spec no funcional). El binding en AppServiceProvider
    | elige uno según este valor.
    |
    | Valores: 'eloquent' (por defecto) | 'json'
    |
    */

    'persistence' => env('SUPERMERCADO_PERSISTENCE', 'eloquent'),

    /*
    |--------------------------------------------------------------------------
    | Directorio de los archivos JSON
    |--------------------------------------------------------------------------
    |
    | Solo aplica cuando persistence = 'json'. Cada adapter escribe un archivo
    | (productos.json, ventas.json, ...) dentro de este directorio.
    |
    */

    'json_dir' => env('SUPERMERCADO_JSON_DIR', storage_path('app/supermercado')),

];
