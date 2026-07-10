<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Supermercado\Application\Stock\RegistrarReposicion;

final class ReposicionController extends Controller
{
    public function __construct(private readonly RegistrarReposicion $replenish) {}

    public function __invoke(string $productId): JsonResponse
    {
        $outcome = $this->replenish->execute($productId);

        return response()->json([
            'productId' => $outcome->result->productId(),
            'moved' => $outcome->result->quantityToMove(),
            'alert' => $outcome->alert !== null
                ? ['productId' => $outcome->alert->productId(), 'ubicacion' => $outcome->alert->ubicacion()->value, 'cantidad' => $outcome->alert->cantidad()]
                : null,
        ]);
    }
}
