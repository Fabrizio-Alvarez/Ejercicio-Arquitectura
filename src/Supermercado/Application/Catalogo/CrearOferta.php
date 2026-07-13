<?php

declare(strict_types=1);

namespace Supermercado\Application\Catalogo;

use Supermercado\Domain\Catalogo\Oferta;
use Supermercado\Domain\Catalogo\OfertaRepository;

/**
 * Caso de uso: CrearOferta.
 *
 * Crea una oferta (descuento porcentual con ventana de vigencia) para un
 * producto. El agregado Oferta valida que el porcentaje esté en [0, 100]
 * y que validTo >= validFrom.
 */
final class CrearOferta
{
    public function __construct(
        private readonly OfertaRepository $ofertas,
    ) {}

    public function execute(string $productoId, float $porcentaje, string $validoDesde, string $validoHasta): Oferta
    {
        $oferta = new Oferta(
            $productoId,
            $porcentaje,
            new \DateTimeImmutable($validoDesde),
            new \DateTimeImmutable($validoHasta),
        );

        $this->ofertas->save($oferta);

        return $oferta;
    }
}
