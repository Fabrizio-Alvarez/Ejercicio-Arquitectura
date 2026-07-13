<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

final class EventoDeDominioModel extends Model
{
    protected $table = 'eventos_de_dominio';

    public $timestamps = false;

    protected $fillable = ['tipo', 'payload', 'occurred_at'];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];
}
