<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;

/**
 * Persiste las góndolas en un archivo JSON en disco. Demuestra que la frontera
 * hexagonal permite cambiar el origen de datos (SQLite/Eloquent → JSON) sin
 * tocar el dominio: este adapter cumple el mismo port que EloquentGondolaRepository.
 */
final class JsonGondolaRepository implements GondolaRepository
{
    use AlmacenaJson;

    protected function nombreArchivo(): string
    {
        return 'gondolas.json';
    }

    public function find(string $productId): ?Gondola
    {
        foreach ($this->leer() as $fila) {
            if (($fila['product_id'] ?? null) === $productId) {
                return $this->aDominio($fila);
            }
        }

        return null;
    }

    public function save(Gondola $shelf): void
    {
        $fila = [
            'product_id' => $shelf->productId(),
            'quantity' => $shelf->quantity(),
        ];

        $filas = $this->leer();
        $reemplazada = false;

        foreach ($filas as $i => $existente) {
            if (($existente['product_id'] ?? null) === $shelf->productId()) {
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
    private function aDominio(array $fila): Gondola
    {
        return new Gondola((string) $fila['product_id'], (int) $fila['quantity']);
    }
}
