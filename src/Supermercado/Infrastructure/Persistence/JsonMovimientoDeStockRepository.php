<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Stock\MovimientoDeStock;
use Supermercado\Domain\Stock\MovimientoDeStockRepository;
use Supermercado\Domain\Stock\TipoDeMovimiento;
use Supermercado\Domain\Stock\UbicacionDeStock;

/**
 * Adapter de persistencia sobre JSON en disco para los movimientos de stock.
 *
 * Cada movimiento es histórico (auditoría), por lo que save() es APPEND: nunca
 * reemplaza una fila existente, siempre agrega una nueva con un id generado.
 */
final class JsonMovimientoDeStockRepository implements MovimientoDeStockRepository
{
    use AlmacenaJson;

    public function save(MovimientoDeStock $movimiento): void
    {
        $filas = $this->leer();

        $filas[] = [
            'id' => $movimiento->id(),
            'producto_id' => $movimiento->productoId(),
            'tipo' => $movimiento->tipo()->value,
            'cantidad' => $movimiento->cantidad(),
            'ubicacion' => $movimiento->ubicacion()->value,
            'referencia' => $movimiento->referencia(),
            'fecha' => $movimiento->fecha()->format(\DateTimeInterface::ATOM),
        ];

        $this->escribir($filas);
    }

    public function all(): array
    {
        $movimientos = array_map(
            fn (array $fila) => $this->aDominio($fila),
            $this->leer(),
        );

        usort($movimientos, fn (MovimientoDeStock $a, MovimientoDeStock $b) => $b->fecha() <=> $a->fecha());

        return $movimientos;
    }

    public function findByProducto(string $productoId): array
    {
        $filas = array_filter(
            $this->leer(),
            fn (array $fila) => (string) ($fila['producto_id'] ?? '') === $productoId,
        );

        $movimientos = array_map(
            fn (array $fila) => $this->aDominio($fila),
            array_values($filas),
        );

        usort($movimientos, fn (MovimientoDeStock $a, MovimientoDeStock $b) => $b->fecha() <=> $a->fecha());

        return $movimientos;
    }

    /** @param array<string, mixed> $fila */
    private function aDominio(array $fila): MovimientoDeStock
    {
        $referencia = $fila['referencia'] ?? null;

        return new MovimientoDeStock(
            (string) $fila['id'],
            (string) $fila['producto_id'],
            TipoDeMovimiento::from((string) $fila['tipo']),
            (int) $fila['cantidad'],
            UbicacionDeStock::from((string) $fila['ubicacion']),
            new \DateTimeImmutable((string) $fila['fecha']),
            $referencia !== null ? (string) $referencia : null,
        );
    }

    protected function nombreArchivo(): string
    {
        return 'movimientos.json';
    }
}
