<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Dinero;

/**
 * Adapter JsonProductoRepository: persiste Producto sobre un archivo JSON en
 * disco (productos.json) usando el trait AlmacenaJson. Demuestra que la frontera
 * hexagonal permite cambiar el origen de datos (Eloquent → JSON) sin tocar el
 * dominio: este adapter implementa el mismo port ProductoRepository que usa el
 * EloquentProductoRepository.
 */
final class JsonProductoRepository implements ProductoRepository
{
    use AlmacenaJson;

    protected function nombreArchivo(): string
    {
        return 'productos.json';
    }

    public function find(string $id): ?Producto
    {
        foreach ($this->leer() as $fila) {
            if ((string) ($fila['id'] ?? '') === $id) {
                return $this->aDominio($fila);
            }
        }

        return null;
    }

    public function save(Producto $product): void
    {
        $fila = [
            'id' => $product->id(),
            'name' => $product->name(),
            'price_amount' => $product->price()->amount(),
            'price_currency' => $product->price()->currency(),
        ];

        $filas = $this->leer();
        $reemplazado = false;

        foreach ($filas as $i => $existente) {
            if ((string) ($existente['id'] ?? '') === $product->id()) {
                $filas[$i] = $fila;
                $reemplazado = true;
                break;
            }
        }

        if (! $reemplazado) {
            $filas[] = $fila;
        }

        $this->escribir($filas);
    }

    public function delete(string $id): void
    {
        $filas = array_values(array_filter(
            $this->leer(),
            fn (array $fila): bool => (string) ($fila['id'] ?? '') !== $id,
        ));

        $this->escribir($filas);
    }

    public function all(): array
    {
        return array_map(
            fn (array $fila): Producto => $this->aDominio($fila),
            $this->leer(),
        );
    }

    /** @param array<string, mixed> $fila */
    private function aDominio(array $fila): Producto
    {
        return new Producto(
            (string) $fila['id'],
            (string) $fila['name'],
            new Dinero((int) $fila['price_amount'], (string) $fila['price_currency']),
        );
    }
}
