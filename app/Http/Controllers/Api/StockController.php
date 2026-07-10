<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Supermercado\Application\Stock\ListarStock;

final class StockController extends Controller
{
    public function __construct(private readonly ListarStock $listStock) {}

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'items' => array_map(fn ($view) => (array) $view, $this->listStock->execute()),
        ]);
    }
}
