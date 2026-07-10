<?php

declare(strict_types=1);

namespace Supermercado\Domain\Catalogo;

/**
 * Colección first-class de Oferta.
 *
 * Encapsula la regla "la mejor oferta activa que cubre a un producto" para que
 * viva con el dominio del catálogo en lugar de repetirse como un loop suelto en
 * un servicio. Es inmutable y valida que sólo contenga instancias de Oferta.
 *
 * @implements \IteratorAggregate<Oferta>
 */
final class Ofertas implements \Countable, \IteratorAggregate
{
    /** @var Oferta[] */
    private readonly array $ofertas;

    /** @param Oferta[] $ofertas */
    public function __construct(array $ofertas = [])
    {
        foreach ($ofertas as $oferta) {
            if (! $oferta instanceof Oferta) {
                throw new \InvalidArgumentException('Ofertas sólo acepta instancias de Oferta.');
            }
        }

        $this->ofertas = array_values($ofertas);
    }

    /**
     * La mejor (mayor porcentaje) oferta activa que cubre al producto en el
     * instante dado, o null si ninguna aplica. Resuelve empates por orden de
     * inserción (gana la primera con el porcentaje máximo).
     */
    public function bestActiveFor(string $productId, \DateTimeImmutable $at): ?Oferta
    {
        $best = null;

        foreach ($this->ofertas as $oferta) {
            if (! $oferta->covers($productId) || ! $oferta->isActive($at)) {
                continue;
            }

            if ($best === null || $oferta->percent() > $best->percent()) {
                $best = $oferta;
            }
        }

        return $best;
    }

    public function count(): int
    {
        return count($this->ofertas);
    }

    /**
     * @return \ArrayIterator<Oferta>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->ofertas);
    }
}
