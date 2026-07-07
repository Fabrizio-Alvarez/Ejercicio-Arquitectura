<?php

declare(strict_types=1);

namespace Supermarket\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent persistence model for products. This is INFRASTRUCTURE: it knows
 * about the database, not about the domain. The repository adapter translates
 * between this and the domain Product.
 */
final class ProductModel extends Model
{
    protected $table = 'products';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'price_amount',
        'price_currency',
    ];
}
