<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Supermercado\Application\Stock\ConfigurarUmbrales;

/**
 * PUT /api/threshold/{productId} — configura umbrales de alerta de stock bajo.
 *
 * Solo el depositista puede configurar los umbrales. El cuerpo incluye
 * umbral_gondola y/o umbral_deposito (enteros >= 0); los omitidos se
 * mantienen en su valor actual.
 */
final class UmbralController extends Controller
{
    public function __invoke(Request $request, string $productId, ConfigurarUmbrales $useCase): JsonResponse
    {
        $data = $request->validate([
            'umbral_gondola' => ['nullable', 'integer', 'min:0'],
            'umbral_deposito' => ['nullable', 'integer', 'min:0'],
        ]);

        $useCase->execute(
            $productId,
            isset($data['umbral_gondola']) ? (int) $data['umbral_gondola'] : null,
            isset($data['umbral_deposito']) ? (int) $data['umbral_deposito'] : null,
        );

        return response()->json(['ok' => true]);
    }
}
