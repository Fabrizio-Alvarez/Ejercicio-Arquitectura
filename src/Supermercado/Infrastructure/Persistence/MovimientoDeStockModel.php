<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

final class MovimientoDeStockModel extends Model
{
    protected $table = 'movimientos_de_stock';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'producto_id',
        'tipo',
        'cantidad',
        'ubicacion',
        'referencia',
        'fecha',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'fecha' => 'datetime',
    ];
}
