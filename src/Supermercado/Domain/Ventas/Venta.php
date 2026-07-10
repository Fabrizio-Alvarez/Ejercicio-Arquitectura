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
 *
 * Invariantes:
 *  - Las líneas solo pueden agregarse mientras la venta está Pendiente.
 *  - Todas las líneas deben compartir la misma moneda.
 *  - Una venta no puede confirmarse sin líneas.
 *  - Las transiciones de estado son explícitas: Pendiente -> Confirmada | Cancelada.
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
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        private readonly MetodoDePago $metodoDePago = MetodoDePago::Efectivo,
    ) {
        $this->status = EstadoDeVenta::Pendiente;
    }

    /**
     * Reconstituye una venta desde estado persistido. Bordea la API pública de
     * mutación y sus invariantes: se usa solo para cargar datos ya válidos y
     * de confianza. No graba eventos (estos ya ocurrieron en el pasado).
     *
     * @param LineaDeVenta[] $lines
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

        $total = $this->lines[0]->total();

        for ($i = 1, $count = count($this->lines); $i < $count; $i++) {
            $total = $total->add($this->lines[$i]->total());
        }

        return $total;
    }

    public function confirm(): void
    {
        $this->ensureStatus(EstadoDeVenta::Pendiente, 'confirmada');

        if ($this->lines === []) {
            throw new \DomainException('Cannot confirm a sale with no lines.');
        }

        $this->status = EstadoDeVenta::Confirmada;

        $this->eventos[] = new CompraRealizada($this->id, $this->metodoDePago, $this->lines);
    }

    public function cancel(): void
    {
        $this->ensureStatus(EstadoDeVenta::Pendiente, 'cancelada');

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
