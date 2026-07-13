<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Supermercado\Application\Stock\RegistrarReabastecimiento;

final class ReabastecimientoController extends Controller
{
    public function __construct(private readonly RegistrarReabastecimiento $restock) {}

    public function __invoke(string $productId, Request $request): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'proveedor' => ['nullable', 'string', 'max:120'],
        ]);

        $outcome = $this->restock->execute($productId, (int) $data['quantity'], $data['proveedor'] ?? null);

        return response()->json([
            'productId' => $outcome->productId,
            'recibido' => $outcome->recibido,
            'nivelDelDeposito' => $outcome->nivelDelDeposito,
        ]);
    }
}
