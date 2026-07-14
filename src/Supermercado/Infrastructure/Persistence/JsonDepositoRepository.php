<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;

/**
 * Persiste los depósitos en un archivo JSON en disco. Demuestra que la frontera
 * hexagonal permite cambiar el origen de datos (SQLite/Eloquent → JSON) sin
 * tocar el dominio: este adapter cumple el mismo port que EloquentDepositoRepository.
 */
final class JsonDepositoRepository implements DepositoRepository
{
    use AlmacenaJson;

    protected function nombreArchivo(): string
    {
        return 'depositos.json';
    }

    public function find(string $productId): ?Deposito
    {
        foreach ($this->leer() as $fila) {
            if (($fila['product_id'] ?? null) === $productId) {
                return $this->aDominio($fila);
            }
        }

        return null;
    }

    public function save(Deposito $warehouse): void
    {
        $fila = [
            'product_id' => $warehouse->productId(),
            'quantity' => $warehouse->quantity(),
            'umbral_bajo' => $warehouse->umbralBajo(),
        ];

        $filas = $this->leer();
        $reemplazada = false;

        foreach ($filas as $i => $existente) {
            if (($existente['product_id'] ?? null) === $warehouse->productId()) {
                $filas[$i] = $fila;
                $reemplazada = true;
                break;
            }
        }

        if (! $reemplazada) {
            $filas[] = $fila;
        }

        $this->escribir($filas);
    }

    public function all(): array
    {
        return array_map(
            fn (array $fila) => $this->aDominio($fila),
            $this->leer(),
        );
    }

    /** @param array<string, mixed> $fila */
    private function aDominio(array $fila): Deposito
    {
        return new Deposito((string) $fila['product_id'], (int) $fila['quantity'], (int) ($fila['umbral_bajo'] ?? Deposito::UMBRAL_BAJO));
    }
}
