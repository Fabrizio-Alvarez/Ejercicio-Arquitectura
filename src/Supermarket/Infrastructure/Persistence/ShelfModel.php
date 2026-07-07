<?php

declare(strict_types=1);

namespace Supermarket\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

final class ShelfModel extends Model
{
    protected $table = 'shelves';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'product_id';

    protected $fillable = ['product_id', 'quantity'];
}
