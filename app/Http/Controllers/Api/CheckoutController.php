<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Supermarket\Application\Sales\CobrarProductos;
use Supermarket\Application\Sales\CobrarRequest;
use Supermarket\Application\Sales\ItemRequest;
use Supermarket\Domain\Sales\Sale;

final class CheckoutController extends Controller
{
    public function __construct(private readonly CobrarProductos $checkout) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'saleId' => ['required', 'string'],
            'cashierId' => ['required', 'string'],
            'customerName' => ['required', 'string'],
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
        ));

        return response()->json($this->toJson($sale), 201);
    }

    private function toJson(Sale $sale): array
    {
        return [
            'id' => $sale->id(),
            'cashierId' => $sale->cashierId(),
            'customerName' => $sale->customerName(),
            'status' => $sale->status()->value,
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
