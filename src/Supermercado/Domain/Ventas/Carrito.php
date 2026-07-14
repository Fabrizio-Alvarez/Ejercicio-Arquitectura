<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

/**
 * Carrito de compra: value object efímero del dominio.
 *
 * Representa la intención de compra antes de que exista una Venta. El cliente
 * acumula productos, puede dejar alguno en la caja (remover) y, cuando decide
 * pagar, el carrito ensambla la venta aplicando pricing vía Cotizador.
 *
 * No se persiste — muere al producir la Venta. Es el concepto que faltaba: el
 * loop de "resolver + cotizar + agregar línea" vivía inline en el caso de uso
 * CobrarProductos porque no había un objeto que lo encapsulara.
 *
 * Es inmutable: agregar() y remover() devuelven una nueva instancia, igual que
 * Dinero y Ofertas en el resto del dominio.
 */
final class Carrito
{
    /** @var ItemCarrito[] */
    private readonly array $items;

    /**
     * @param ItemCarrito[] $items
     */
    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
    }

    /**
     * Devuelve un nuevo carrito con el item agregado (inmutable).
     */
    public function agregar(ItemCarrito $item): self
    {
        return new self([...$this->items, $item]);
    }

    /**
     * Devuelve un nuevo carrito sin el producto indicado.
     *
     * Modela "dejar un producto en la caja": el cliente decide no llevarlo
     * antes de que el cajero cobre. Si el producto no estaba, es no-op.
     */
    public function remover(string $productoId): self
    {
        return new self(array_filter(
            $this->items,
            fn (ItemCarrito $i) => $i->producto->id() !== $productoId,
        ));
    }

    /**
     * Ensambla una Venta (Pendiente) aplicando pricing a cada item vía Cotizador.
     *
     * No confirma la venta — esa decisión es del caso de uso (o, en el futuro,
     * del orquestador de pago). El carrito produce; el contexto de aplicación
     * compromete.
     *
     * @throws \DomainException si el carrito está vacío.
     */
    public function cobrar(
        Cotizador $pricer,
        \DateTimeImmutable $now,
        string $ventaId,
        string $cajeroId,
        string $cliente,
        MetodoDePago $metodoDePago,
    ): Venta {
        if ($this->items === []) {
            throw new \DomainException('No se puede cobrar un carrito vacío.');
        }

        $venta = new Venta($ventaId, $cajeroId, $cliente, $now, $metodoDePago);

        foreach ($this->items as $item) {
            $venta->addLine(
                $pricer->price($item->producto, $item->cantidad, $item->ofertas, $now),
            );
        }

        $venta->marcarEsperandoPago();

        return $venta;
    }

    /**
     * @return ItemCarrito[]
     */
    public function items(): array
    {
        return $this->items;
    }

    public function cantidad(): int
    {
        return count($this->items);
    }

    public function estaVacio(): bool
    {
        return $this->items === [];
    }
}
