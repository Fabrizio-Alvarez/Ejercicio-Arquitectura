<?php

declare(strict_types=1);

namespace Supermercado\Infrastructure\Persistence;

use Supermercado\Domain\Catalogo\Oferta;
use Supermercado\Domain\Catalogo\OfertaRepository;

/**
 * Adapter JsonOfertaRepository: persiste Oferta sobre un archivo JSON en
 * disco (ofertas.json) usando el trait AlmacenaJson.
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

    public function save(Oferta $oferta): void
    {
        $filas = $this->leer();
        $filas[] = [
            'product_id' => $oferta->productId(),
            'percent' => $oferta->percent(),
            'valid_from' => $oferta->validFrom()->format('Y-m-d H:i:s'),
            'valid_to' => $oferta->validTo()->format('Y-m-d H:i:s'),
        ];
        $this->escribir($filas);
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
