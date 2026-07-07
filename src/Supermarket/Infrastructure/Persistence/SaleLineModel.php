<?php

declare(strict_types=1);

namespace Supermarket\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SaleLineModel extends Model
{
    protected $table = 'sale_lines';

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
     * @return BelongsTo<SaleModel>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(SaleModel::class, 'sale_id');
    }
}
