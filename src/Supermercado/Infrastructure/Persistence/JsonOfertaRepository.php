<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Catalogo\Oferta;
use Supermercado\Domain\Catalogo\OfertaRepository;

/**
 * Adapter JsonOfertaRepository: lee Oferta desde un archivo JSON en disco
 * (ofertas.json) usando el trait AlmacenaJson.
 *
 * El port OfertaRepository es read-only: las ofertas las carga un sistema
 * externo directamente en el origen de datos (per spec). Por eso este adapter
 * NO expone save() — solo hidrata filas a Oferta.
 *
 * rutaDeArchivo() se expone únicamente para que los tests puedan sembrar el
 * archivo JSON a mano; no forma parte del contrato del dominio.
 */
final class JsonOfertaRepository implements OfertaRepository
{
    use AlmacenaJson;

    protected function nombreArchivo(): string
    {
        return 'ofertas.json';
    }

    public function findByProduct(string $productId): array
    {
        $coinciden = array_filter(
            $this->leer(),
            fn (array $fila): bool => (string) ($fila['product_id'] ?? '') === $productId,
        );

        return array_map(
            fn (array $fila): Oferta => $this->aDominio($fila),
            array_values($coinciden),
        );
    }

    public function all(): array
    {
        return array_map(
            fn (array $fila): Oferta => $this->aDominio($fila),
            $this->leer(),
        );
    }

    /**
     * Ruta completa del archivo JSON. Expuesta para que los tests siembren
     * datos a mano sin romper el contrato read-only del port.
     */
    public function rutaDeArchivo(): string
    {
        return $this->ruta();
    }

    /** @param array<string, mixed> $fila */
    private function aDominio(array $fila): Oferta
    {
        return new Oferta(
            (string) $fila['product_id'],
            (float) $fila['percent'],
            new \DateTimeImmutable((string) $fila['valid_from']),
            new \DateTimeImmutable((string) $fila['valid_to']),
        );
    }
}
