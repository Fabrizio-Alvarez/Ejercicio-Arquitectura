<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Supermercado\Application\Ventas\ProcesarDevolucion;
use Supermercado\Domain\Ventas\ItemDevolucion;

final class DevolucionController extends Controller
{
    public function __construct(private readonly ProcesarDevolucion $devoluciones) {}

    public function __invoke(Request $request, string $ventaId): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.productId' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $items = array_map(
            fn (array $item) => new ItemDevolucion($item['productId'], $item['quantity']),
            $data['items'],
        );

        $this->devoluciones->execute($ventaId, $items);

        return response()->json(['status' => 'ok', 'ventaId' => $ventaId], 200);
    }
}
