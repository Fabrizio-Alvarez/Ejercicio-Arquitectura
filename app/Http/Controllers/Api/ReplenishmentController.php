<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Supermarket\Application\Stock\RegistrarReposicion;

final class ReplenishmentController extends Controller
{
    public function __construct(private readonly RegistrarReposicion $replenish) {}

    public function __invoke(string $productId): JsonResponse
    {
        $outcome = $this->replenish->execute($productId);

        return response()->json([
            'productId' => $outcome->result->productId(),
            'moved' => $outcome->result->quantityToMove(),
            'alert' => $outcome->alert !== null
                ? ['productId' => $outcome->alert->productId(), 'warehouseQuantity' => $outcome->alert->warehouseQuantity()]
                : null,
        ]);
    }
}
