<?php

declare(strict_types=1);

namespace Supermarket\Infrastructure\Persistence;

use Illuminate\Support\Facades\DB;
use Supermarket\Domain\Sales\Sale;
use Supermarket\Domain\Sales\SaleLine;
use Supermarket\Domain\Sales\SaleRepository;
use Supermarket\Domain\Sales\SaleStatus;
use Supermarket\Domain\Shared\Money;

final class EloquentSaleRepository implements SaleRepository
{
    public function find(string $id): ?Sale
    {
        /** @var SaleModel|null $row */
        $row = SaleModel::with('lineRecords')->find($id);

        return $row !== null ? $this->toDomain($row) : null;
    }

    public function save(Sale $sale): void
    {
        DB::transaction(function () use ($sale): void {
            SaleModel::updateOrCreate(
                ['id' => $sale->id()],
                [
                    'cashier_id' => $sale->cashierId(),
                    'customer_name' => $sale->customerName(),
                    'status' => $sale->status()->value,
                    'sold_at' => $sale->createdAt()->format(\DateTimeInterface::ATOM),
                ],
            );

            // Replace the sale's lines atomically.
            SaleLineModel::where('sale_id', $sale->id())->delete();

            foreach ($sale->lines() as $line) {
                SaleLineModel::create([
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
        return SaleModel::with('lineRecords')
            ->get()
            ->map(fn (SaleModel $row) => $this->toDomain($row))
            ->all();
    }

    private function toDomain(SaleModel $row): Sale
    {
        $lines = $row->lineRecords
            ->map(fn (SaleLineModel $lineRow) => $this->lineToDomain($lineRow))
            ->all();

        return Sale::reconstitute(
            (string) $row->id,
            (string) $row->cashier_id,
            (string) $row->customer_name,
            new \DateTimeImmutable((string) $row->sold_at),
            SaleStatus::from((string) $row->status),
            $lines,
        );
    }

    private function lineToDomain(SaleLineModel $lineRow): SaleLine
    {
        return new SaleLine(
            (string) $lineRow->product_id,
            (string) $lineRow->product_name,
            (int) $lineRow->quantity,
            new Money((int) $lineRow->unit_price_amount, (string) $lineRow->unit_price_currency),
        );
    }
}
