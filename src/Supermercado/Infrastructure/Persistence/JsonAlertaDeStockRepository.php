<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Illuminate\Support\Str;
use Supermercado\Domain\Stock\AlertaDeStock;
use Supermercado\Domain\Stock\AlertaDeStockRepository;
use Supermercado\Domain\Stock\UbicacionDeStock;

/**
 * Adapter de persistencia sobre JSON en disco para las alertas de stock bajo.
 *
 * La alerta es un evento/valor del dominio sin identidad propia, por lo que
 * save() es APPEND y genera su propio id de persistencia con Str::uuid().
 */
final class JsonAlertaDeStockRepository implements AlertaDeStockRepository
{
    use AlmacenaJson;

    public function save(AlertaDeStock $alerta): void
    {
        $filas = $this->leer();

        $filas[] = [
            'id' => Str::uuid()->toString(),
            'producto_id' => $alerta->productId(),
            'ubicacion' => $alerta->ubicacion()->value,
            'cantidad' => $alerta->cantidad(),
            'fecha' => $alerta->at()->format(\DateTimeInterface::ATOM),
        ];

        $this->escribir($filas);
    }

    public function all(): array
    {
        $alertas = array_map(
            fn (array $fila) => $this->aDominio($fila),
            $this->leer(),
        );

        usort($alertas, fn (AlertaDeStock $a, AlertaDeStock $b) => $b->at() <=> $a->at());

        return $alertas;
    }

    public function findByProducto(string $productoId): array
    {
        $filas = array_filter(
            $this->leer(),
            fn (array $fila) => (string) ($fila['producto_id'] ?? '') === $productoId,
        );

        $alertas = array_map(
            fn (array $fila) => $this->aDominio($fila),
            array_values($filas),
        );

        usort($alertas, fn (AlertaDeStock $a, AlertaDeStock $b) => $b->at() <=> $a->at());

        return $alertas;
    }

    /** @param array<string, mixed> $fila */
    private function aDominio(array $fila): AlertaDeStock
    {
        return new AlertaDeStock(
            (string) $fila['producto_id'],
            UbicacionDeStock::from((string) $fila['ubicacion']),
            (int) $fila['cantidad'],
            new \DateTimeImmutable((string) $fila['fecha']),
        );
    }

    protected function nombreArchivo(): string
    {
        return 'alertas.json';
    }
}
