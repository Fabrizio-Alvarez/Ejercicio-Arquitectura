<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Ventas\EstadoDeVenta;
use Supermercado\Domain\Ventas\LineaDeVenta;
use Supermercado\Domain\Ventas\MetodoDePago;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\VentaRepository;

/**
 * Adapter de persistencia sobre JSON en disco para el aggregate Venta.
 *
 * Venta es un aggregate con líneas embebidas: la fila serializa sus líneas
 * como un sub-array `lines`, de modo que una venta se persiste y se reconstituye
 * como una sola unidad (no hay tabla de líneas separada). save() es upsert por
 * id: reemplaza la fila cuyo id coincide o agrega una nueva si no existe.
 *
 * La hidratación (dominio ↔ fila) espeja a EloquentVentaRepository::toDomain,
 * demostrando que la frontera hexagonal permite cambiar el origen de datos
 * (SQLite/Eloquent → JSON) sin tocar el dominio.
 */
final class JsonVentaRepository implements VentaRepository
{
    use AlmacenaJson;

    public function find(string $id): ?Venta
    {
        foreach ($this->leer() as $fila) {
            if ((string) ($fila['id'] ?? '') === $id) {
                return $this->aDominio($fila);
            }
        }

        return null;
    }

    public function save(Venta $sale): void
    {
        $filas = $this->leer();

        $fila = $this->aFila($sale);

        $reemplazada = false;
        foreach ($filas as $indice => $existente) {
            if ((string) ($existente['id'] ?? '') === $sale->id()) {
                $filas[$indice] = $fila;
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
            fn (array $fila): Venta => $this->aDominio($fila),
            $this->leer(),
        );
    }

    protected function nombreArchivo(): string
    {
        return 'ventas.json';
    }

    /** @param array<string, mixed> $fila */
    private function aDominio(array $fila): Venta
    {
        $lineas = array_map(
            fn (array $linea): LineaDeVenta => new LineaDeVenta(
                (string) $linea['product_id'],
                (string) $linea['product_name'],
                (int) $linea['quantity'],
                new Dinero((int) $linea['unit_price_amount'], (string) $linea['unit_price_currency']),
            ),
            (array) ($fila['lines'] ?? []),
        );

        return Venta::reconstitute(
            (string) $fila['id'],
            (string) $fila['cashier_id'],
            (string) $fila['customer_name'],
            MetodoDePago::from((string) $fila['payment_method']),
            new \DateTimeImmutable((string) $fila['sold_at']),
            EstadoDeVenta::from((string) $fila['status']),
            $lineas,
        );
    }

    /** @return array<string, mixed> */
    private function aFila(Venta $sale): array
    {
        return [
            'id' => $sale->id(),
            'cashier_id' => $sale->cashierId(),
            'customer_name' => $sale->customerName(),
            'payment_method' => $sale->metodoDePago()->value,
            'status' => $sale->status()->value,
            'sold_at' => $sale->createdAt()->format(\DateTimeInterface::ATOM),
            'lines' => array_map(
                fn (LineaDeVenta $linea): array => [
                    'product_id' => $linea->productId(),
                    'product_name' => $linea->productName(),
                    'quantity' => $linea->quantity(),
                    'unit_price_amount' => $linea->unitPrice()->amount(),
                    'unit_price_currency' => $linea->unitPrice()->currency(),
                ],
                $sale->lines(),
            ),
        ];
    }
}
