<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Supermercado\Application\Ventas\CobrarProductos;
use Supermercado\Application\Ventas\CobrarRequest;
use Supermercado\Application\Ventas\ItemRequest;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\MetodoDePago;

final class CobroController extends Controller
{
    public function __construct(private readonly CobrarProductos $checkout) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'saleId' => ['required', 'string'],
            'cashierId' => ['required', 'string'],
            'customerName' => ['required', 'string'],
            'paymentMethod' => ['required', 'string', 'in:efectivo,tarjeta_credito,tarjeta_debito,transferencia,qr'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.productId' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $items = array_map(
            fn (array $item) => new ItemRequest($item['productId'], $item['quantity']),
            $data['items'],
        );

        $sale = $this->checkout->execute(new CobrarRequest(
            $data['saleId'],
            $data['cashierId'],
            $data['customerName'],
            $items,
            MetodoDePago::from($data['paymentMethod']),
        ));

        return response()->json($this->toJson($sale), 201);
    }

    private function toJson(Venta $sale): array
    {
        return [
            'id' => $sale->id(),
            'cashierId' => $sale->cashierId(),
            'customerName' => $sale->customerName(),
            'status' => $sale->status()->value,
            'paymentMethod' => $sale->metodoDePago()->value,
            'total' => ['amount' => $sale->total()->amount(), 'currency' => $sale->total()->currency()],
            'lines' => array_map(fn ($line) => [
                'productId' => $line->productId(),
                'productName' => $line->productName(),
                'quantity' => $line->quantity(),
                'unitPrice' => ['amount' => $line->unitPrice()->amount(), 'currency' => $line->unitPrice()->currency()],
            ], $sale->lines()),
        ];
    }
}
