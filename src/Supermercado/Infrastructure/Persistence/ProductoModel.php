<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent persistence model for products. This is INFRASTRUCTURE: it knows
 * about the database, not about the domain. The repository adapter translates
 * between this and the domain Producto.
 */
final class ProductoModel extends Model
{
    protected $table = 'productos';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'price_amount',
        'price_currency',
    ];
}
