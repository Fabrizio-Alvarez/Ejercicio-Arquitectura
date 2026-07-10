<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Illuminate\Support\Facades\DB;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\LineaDeVenta;
use Supermercado\Domain\Ventas\VentaRepository;
use Supermercado\Domain\Ventas\EstadoDeVenta;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Ventas\MetodoDePago;

final class EloquentVentaRepository implements VentaRepository
{
    public function find(string $id): ?Venta
    {
        /** @var VentaModel|null $row */
        $row = VentaModel::with('lineRecords')->find($id);

        return $row !== null ? $this->toDomain($row) : null;
    }

    public function save(Venta $sale): void
    {
        DB::transaction(function () use ($sale): void {
            VentaModel::updateOrCreate(
                ['id' => $sale->id()],
                [
                    'cashier_id' => $sale->cashierId(),
                    'customer_name' => $sale->customerName(),
                    'payment_method' => $sale->metodoDePago()->value,
                    'status' => $sale->status()->value,
                    'sold_at' => $sale->createdAt()->format(\DateTimeInterface::ATOM),
                ],
            );

            // Replace the sale's lines atomically.
            LineaDeVentaModel::where('sale_id', $sale->id())->delete();

            foreach ($sale->lines() as $line) {
                LineaDeVentaModel::create([
                    'sale_id' => $sale->id(),
                    'product_id' => $line->productId(),
                    'product_name' => $line->productName(),
                    'quantity' => $line->quantity(),
                    'unit_price_amount' => $line->unitPrice()->amount(),
                    'unit_price_currency' => $line->unitPrice()->currency(),
                ]);
            }
        });
    }

    public function all(): array
    {
        return VentaModel::with('lineRecords')
            ->get()
            ->map(fn (VentaModel $row) => $this->toDomain($row))
            ->all();
    }

    private function toDomain(VentaModel $row): Venta
    {
        $lines = $row->lineRecords
            ->map(fn (LineaDeVentaModel $lineRow) => $this->lineToDomain($lineRow))
            ->all();

        return Venta::reconstitute(
            (string) $row->id,
            (string) $row->cashier_id,
            (string) $row->customer_name,
            MetodoDePago::from((string) $row->payment_method),
            new \DateTimeImmutable((string) $row->sold_at),
            EstadoDeVenta::from((string) $row->status),
            $lines,
        );
    }

    private function lineToDomain(LineaDeVentaModel $lineRow): LineaDeVenta
    {
        return new LineaDeVenta(
            (string) $lineRow->product_id,
            (string) $lineRow->product_name,
            (int) $lineRow->quantity,
            new Dinero((int) $lineRow->unit_price_amount, (string) $lineRow->unit_price_currency),
        );
    }
}
