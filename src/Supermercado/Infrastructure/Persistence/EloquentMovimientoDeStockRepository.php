<?php
declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Stock\MovimientoDeStock;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;

final class EloquentMovimientoDeStockRepository implements MovimientoDeStockRepository
{
    public function save(MovimientoDeStock $movimiento): void
    {
        MovimientoDeStockModel::updateOrCreate(
            ['id' => $movimiento->id()],
            [
                'producto_id' => $movimiento->productoId(),
                'tipo' => $movimiento->tipo()->value,
                'cantidad' => $movimiento->cantidad(),
                'ubicacion' => $movimiento->ubicacion()->value,
                'referencia' => $movimiento->referencia(),
                'fecha' => $movimiento->fecha()->format(\DateTimeInterface::ATOM),
            ],
        );
    }

    public function all(): array
    {
        return MovimientoDeStockModel::query()
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn (MovimientoDeStockModel $row) => $this->toDomain($row))
            ->all();
    }

    public function findByProducto(string $productoId): array
    {
        return MovimientoDeStockModel::where('producto_id', $productoId)
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn (MovimientoDeStockModel $row) => $this->toDomain($row))
            ->all();
    }

    private function toDomain(MovimientoDeStockModel $row): MovimientoDeStock
    {
        /** @var \Illuminate\Support\Carbon $fecha */
        $fecha = $row->fecha;

        return new MovimientoDeStock(
            (string) $row->id,
            (string) $row->producto_id,
            TipoDeMovimiento::from((string) $row->tipo),
            (int) $row->cantidad,
            UbicacionDeStock::from((string) $row->ubicacion),
            $fecha->toDateTimeImmutable(),
            $row->referencia !== null ? (string) $row->referencia : null,
        );
    }
}
