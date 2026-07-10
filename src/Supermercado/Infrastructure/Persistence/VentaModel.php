<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class VentaModel extends Model
{
    protected $table = 'ventas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'cashier_id',
        'customer_name',
        'payment_method',
        'status',
        'sold_at',
    ];

    /**
     * @return HasMany<LineaDeVentaModel>
     */
    public function lineRecords(): HasMany
    {
        return $this->hasMany(LineaDeVentaModel::class, 'sale_id');
    }
}
