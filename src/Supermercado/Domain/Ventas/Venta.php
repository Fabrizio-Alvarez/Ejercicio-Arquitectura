<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

use Supermercado\Domain\Comun\Dinero;

/**
 * Aggregate root de Venta.
 *
 * Toda la venta se piensa como un único agregado: registra las líneas vendidas,
 * el total, el método de pago con el que el cliente abonó y su estado. Al
 * confirmarse graba un evento de dominio CompraRealizada para que reaccionen
 * los interesados (descontar stock de la góndola, avisar al depósito, ...).
 * registrarDevolucion graba DevolucionRegistrada para restaurar stock devuelto.
 *
 * Invariantes:
 *  - Las líneas solo pueden agregarse mientras la venta está Pendiente.
 *  - Todas las líneas deben compartir la misma moneda.
 *  - Una venta no puede confirmarse sin líneas.
 *  - Las transiciones de estado son explícitas:
 *    Pendiente → EsperandoPago → Confirmada | Cancelada.
 */
final class Venta
{
    /** @var LineaDeVenta[] */
    private array $lines = [];

    /** @var object[] eventos de dominio grabados por el agregado. */
    private array $eventos = [];

    private EstadoDeVenta $status;

    public function __construct(
        private readonly string $id,
        private readonly string $cashierId,
        private readonly string $customerName,
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable,
        private readonly MetodoDePago $metodoDePago = MetodoDePago::Efectivo,
    ) {
        $this->status = EstadoDeVenta::Pendiente;
    }

    /**
     * Reconstituye una venta desde estado persistido. Bordea la API pública de
     * mutación y sus invariantes: se usa solo para cargar datos ya válidos y
     * de confianza. No graba eventos (estos ya ocurrieron en el pasado).
     *
     * @param  LineaDeVenta[]  $lines
     */
    public static function reconstitute(
        string $id,
        string $cashierId,
        string $customerName,
        MetodoDePago $metodoDePago,
        \DateTimeImmutable $createdAt,
        EstadoDeVenta $status,
        array $lines,
    ): self {
        $sale = new self($id, $cashierId, $customerName, $createdAt, $metodoDePago);
        $sale->status = $status;

        foreach ($lines as $line) {
            $sale->lines[] = $line;
        }

        return $sale;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function cashierId(): string
    {
        return $this->cashierId;
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function metodoDePago(): MetodoDePago
    {
        return $this->metodoDePago;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function status(): EstadoDeVenta
    {
        return $this->status;
    }

    public function isPending(): bool
    {
        return $this->status === EstadoDeVenta::Pendiente;
    }

    public function isConfirmed(): bool
    {
        return $this->status === EstadoDeVenta::Confirmada;
    }

    public function isCancelled(): bool
    {
        return $this->status === EstadoDeVenta::Cancelada;
    }

    public function isEsperandoPago(): bool
    {
        return $this->status === EstadoDeVenta::EsperandoPago;
    }

    /** ¿Pertenece esta venta a este cajero? Regla de negocio del cierre de caja. */
    public function isForCashier(string $cashierId): bool
    {
        return $this->cashierId === $cashierId;
    }

    /** ¿Ocurrió esta venta en el día calendario dado? Compara Y-m-d. */
    public function isOnDay(\DateTimeImmutable $day): bool
    {
        return $this->createdAt->format('Y-m-d') === $day->format('Y-m-d');
    }

    /** @return LineaDeVenta[] */
    public function lines(): array
    {
        return $this->lines;
    }

    /** @return object[] eventos de dominio grabados (p.ej. CompraRealizada). */
    public function eventos(): array
    {
        return $this->eventos;
    }

    public function addLine(LineaDeVenta $line): void
    {
        $this->ensureEditable();

        if ($this->lines !== [] && $line->currency() !== $this->lines[0]->currency()) {
            throw new \InvalidArgumentException('All sale lines must share the same currency.');
        }

        $this->lines[] = $line;
    }

    public function total(): Dinero
    {
        if ($this->lines === []) {
            throw new \DomainException('Cannot compute the total of a sale with no lines.');
        }

        return Dinero::sum(...array_map(fn (LineaDeVenta $line) => $line->total(), $this->lines));
    }

    /** Cantidad de líneas distintas en la venta. */
    public function lineCount(): int
    {
        return count($this->lines);
    }

    /** Cantidad total de unidades vendidas (suma de las cantidades de las líneas). */
    public function itemCount(): int
    {
        return array_sum(array_map(fn (LineaDeVenta $line) => $line->quantity(), $this->lines));
    }

    /**
     * Transición Pendiente → EsperandoPago. La venta fue ensamblada por el
     * Carrito y ahora espera confirmación de pago. A partir de acá no se
     * pueden agregar ni quitar líneas.
     */
    public function marcarEsperandoPago(): void
    {
        $this->ensureStatus(EstadoDeVenta::Pendiente, 'marcada como esperando pago');

        $this->status = EstadoDeVenta::EsperandoPago;
    }

    public function confirm(): void
    {
        $this->ensureStatus(EstadoDeVenta::EsperandoPago, 'confirmada');

        if ($this->lines === []) {
            throw new \DomainException('Cannot confirm a sale with no lines.');
        }

        $this->status = EstadoDeVenta::Confirmada;

        $this->eventos[] = new CompraRealizada($this->id, $this->metodoDePago, $this->lines);
    }

    /**
     * Registra una devolución parcial o total sobre la venta confirmada.
     * Cada item indica qué producto y cuántas unidades se devuelven. Lanza si:
     *  - La venta no está Confirmada.
     *  - Algún producto no pertenece a la venta.
     *  - La cantidad a devolver excede la vendida.
     *
     * @param  ItemDevolucion[]  $items
     */
    public function registrarDevolucion(array $items): void
    {
        if ($this->status !== EstadoDeVenta::Confirmada) {
            throw new \DomainException("Solo las ventas confirmadas pueden recibir devoluciones (estado actual: {$this->status->value}).");
        }

        if ($items === []) {
            throw new \DomainException('La devolución debe contener al menos un item.');
        }

        foreach ($items as $item) {
            $vendido = 0;
            foreach ($this->lines as $linea) {
                if ($linea->productId() === $item->productoId()) {
                    $vendido += $linea->quantity();
                }
            }

            if ($vendido === 0) {
                throw new \DomainException("El producto {$item->productoId()} no pertenece a la venta {$this->id}.");
            }

            if ($item->cantidad() > $vendido) {
                throw new \DomainException("No se pueden devolver {$item->cantidad()} unidades del producto {$item->productoId()}: solo se vendieron {$vendido}.");
            }
        }

        $this->eventos[] = new DevolucionRegistrada($this->id, $items);
    }

    public function cancel(): void
    {
        $this->ensureStatus(EstadoDeVenta::EsperandoPago, 'cancelada');

        $this->status = EstadoDeVenta::Cancelada;
    }

    private function ensureEditable(): void
    {
        if ($this->status !== EstadoDeVenta::Pendiente) {
            throw new \DomainException("Venta is not editable in status {$this->status->value}.");
        }
    }

    private function ensureStatus(EstadoDeVenta $expected, string $action): void
    {
        if ($this->status !== $expected) {
            throw new \DomainException("Only {$expected->value} sales can be {$action}.");
        }
    }
}
