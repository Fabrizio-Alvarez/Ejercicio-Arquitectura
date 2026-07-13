<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Supermercado\Application\Stock\RegistrarAjuste;
use Supermercado\Domain\Stock\UbicacionDeStock;

/**
 * POST /api/adjust/{productId} — ajuste manual de stock.
 *
 * Solo el depositista puede corregir el inventario. El cuerpo incluye
 * ubicación (góndola/depósito), delta (entero con signo) y motivo opcional.
 */
final class AjusteController extends Controller
{
    public function __invoke(Request $request, string $productId, RegistrarAjuste $useCase): JsonResponse
    {
        $data = $request->validate([
            'ubicacion' => ['required', 'string', 'in:gondola,deposito'],
            'delta' => ['required', 'integer'],
            'motivo' => ['nullable', 'string'],
        ]);

        $useCase->execute(
            $productId,
            UbicacionDeStock::from($data['ubicacion']),
            $data['delta'],
            $data['motivo'] ?? null,
        );

        return response()->json(['ok' => true]);
    }
}
