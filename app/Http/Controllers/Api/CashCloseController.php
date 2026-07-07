<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Supermarket\Application\Sales\ObtenerCierreDeCaja;

final class CashCloseController extends Controller
{
    public function __construct(private readonly ObtenerCierreDeCaja $cashClose) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cashierId' => ['required', 'string'],
            'date' => ['required', 'date'],
        ]);

        $close = $this->cashClose->execute($data['cashierId'], new \DateTimeImmutable($data['date']));

        return response()->json([
            'cashierId' => $close->cashierId(),
            'day' => $close->day()->format('Y-m-d'),
            'count' => $close->count(),
            'total' => $close->count() > 0
                ? ['amount' => $close->total()->amount(), 'currency' => $close->total()->currency()]
                : null,
            'rows' => array_map(fn ($row) => [
                'customerName' => $row->customerName(),
                'amount' => ['amount' => $row->amount()->amount(), 'currency' => $row->amount()->currency()],
                'cashierId' => $row->cashierId(),
            ], $close->rows()),
        ]);
    }
}
