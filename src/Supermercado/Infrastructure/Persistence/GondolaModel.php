<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

final class GondolaModel extends Model
{
    protected $table = 'gondolas';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'product_id';

    protected $fillable = ['product_id', 'quantity', 'umbral_bajo'];
}
