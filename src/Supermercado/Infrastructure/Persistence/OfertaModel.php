<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

final class OfertaModel extends Model
{
    protected $table = 'ofertas';

    protected $fillable = [
        'product_id',
        'percent',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'percent' => 'float',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];
}
