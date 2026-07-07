<?php

declare(strict_types=1);

namespace Supermarket\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SaleModel extends Model
{
    protected $table = 'sales';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'cashier_id',
        'customer_name',
        'status',
        'sold_at',
    ];

    /**
     * @return HasMany<SaleLineModel>
     */
    public function lineRecords(): HasMany
    {
        return $this->hasMany(SaleLineModel::class, 'sale_id');
    }
}
