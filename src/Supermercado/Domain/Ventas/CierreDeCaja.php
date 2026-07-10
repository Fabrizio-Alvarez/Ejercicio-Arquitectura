<?php

declare(strict_types=1);

namespace Supermercado\Domain\Ventas;

use Supermercado\Domain\Comun\Dinero;

/**
 * Cash close (cierre de caja): the report of confirmed sales for a single
 * cashier on a single day. Built by filtering a set of sales.
 */
final class CierreDeCaja
{
    /** @var ResumenDeVenta[] */
    private array $rows = [];

    private function __construct(
        private readonly string $cashierId,
        private readonly \DateTimeImmutable $day,
    ) {}

    /**
     * Build a cash close from the given sales, keeping only the confirmed
     * sales of this cashier made on this day.
     */
    public static function forCashierOn(string $cashierId, \DateTimeImmutable $day, Venta ...$sales): self
    {
        $close = new self($cashierId, $day);

        foreach ($sales as $sale) {
            if ($sale->cashierId() !== $cashierId) {
                continue;
            }

            if ($sale->status() !== EstadoDeVenta::Confirmada) {
                continue;
            }

            if (! self::sameDay($sale->createdAt(), $day)) {
                continue;
            }

            $close->rows[] = new ResumenDeVenta(
                $sale->id(),
                $sale->customerName(),
                $sale->total(),
                $sale->cashierId(),
            );
        }

        return $close;
    }

    public function cashierId(): string
    {
        return $this->cashierId;
    }

    public function day(): \DateTimeImmutable
    {
        return $this->day;
    }

    /** @return ResumenDeVenta[] */
    public function rows(): array
    {
        return $this->rows;
    }

    public function count(): int
    {
        return count($this->rows);
    }

    public function total(): Dinero
    {
        if ($this->rows === []) {
            throw new \DomainException('Cannot total an empty cash close.');
        }

        $total = $this->rows[0]->amount();

        for ($i = 1, $count = count($this->rows); $i < $count; $i++) {
            $total = $total->add($this->rows[$i]->amount());
        }

        return $total;
    }

    private static function sameDay(\DateTimeImmutable $a, \DateTimeImmutable $b): bool
    {
        return $a->format('Y-m-d') === $b->format('Y-m-d');
    }
}
