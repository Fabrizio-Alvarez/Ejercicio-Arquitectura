<?php

declare(strict_types=1);

namespace Supermarket\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

final class OfferModel extends Model
{
    protected $table = 'offers';

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
