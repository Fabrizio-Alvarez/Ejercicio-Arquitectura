<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Illuminate\Support\Str;
use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Stock\AlertaDeStockRepository;
use Supermercado\Domain\Stock\UbicacionDeStock;

final class EloquentAlertaDeStockRepository implements AlertaDeStockRepository
{
    public function save(AlertaDeStock $alerta): void
    {
        AlertaDeStockModel::create([
            'id' => Str::uuid()->toString(),
            'producto_id' => $alerta->productId(),
            'ubicacion' => $alerta->ubicacion()->value,
            'cantidad' => $alerta->cantidad(),
            'fecha' => $alerta->at()->format(\DateTimeInterface::ATOM),
        ]);
    }

    public function all(): array
    {
        return AlertaDeStockModel::query()
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn (AlertaDeStockModel $row) => $this->toDomain($row))
            ->all();
    }

    public function findByProducto(string $productoId): array
    {
        return AlertaDeStockModel::where('producto_id', $productoId)
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn (AlertaDeStockModel $row) => $this->toDomain($row))
            ->all();
    }

    private function toDomain(AlertaDeStockModel $row): AlertaDeStock
    {
        /** @var \Illuminate\Support\Carbon $fecha */
        $fecha = $row->fecha;

        return new AlertaDeStock(
            (string) $row->producto_id,
            UbicacionDeStock::from((string) $row->ubicacion),
            (int) $row->cantidad,
            $fecha->toDateTimeImmutable(),
        );
    }
}
