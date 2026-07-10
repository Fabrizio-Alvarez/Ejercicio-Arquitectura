<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LineaDeVentaModel extends Model
{
    protected $table = 'lineas_de_venta';

    public $timestamps = false;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price_amount',
        'unit_price_currency',
    ];

    /**
     * @return BelongsTo<VentaModel>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(VentaModel::class, 'sale_id');
    }
}
