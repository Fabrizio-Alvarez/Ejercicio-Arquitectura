<?php

declare(strict_types=1);

namespace Supermarket\Domain\Sales;

use Supermarket\Domain\Shared\Money;

/**
 * Sale aggregate root.
 *
 * Enforces its own invariants:
 *  - Lines can only be added while the sale is Pending.
 *  - All lines must share the same currency.
 *  - A sale cannot be confirmed without lines.
 *  - State transitions are explicit: Pending -> Confirmed | Cancelled.
 */
final class Sale
{
    /** @var SaleLine[] */
    private array $lines = [];

    private SaleStatus $status;

    public function __construct(
        private readonly string $id,
        private readonly string $cashierId,
        private readonly string $customerName,
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        $this->status = SaleStatus::Pending;
    }
    /**
     * Reconstitute a sale from persisted state. Bypasses the public mutating
     * API and its invariants — used only to load trusted, already-valid data.
     *
     * @param SaleLine[] $lines
     */
    public static function reconstitute(
        string $id,
        string $cashierId,
        string $customerName,
        \DateTimeImmutable $createdAt,
        SaleStatus $status,
        array $lines,
    ): self {
        $sale = new self($id, $cashierId, $customerName, $createdAt);
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

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function status(): SaleStatus
    {
        return $this->status;
    }

    /** @return SaleLine[] */
    public function lines(): array
    {
        return $this->lines;
    }

    public function addLine(SaleLine $line): void
    {
        $this->ensureEditable();

        if ($this->lines !== [] && $line->currency() !== $this->lines[0]->currency()) {
            throw new \InvalidArgumentException('All sale lines must share the same currency.');
        }

        $this->lines[] = $line;
    }

    public function total(): Money
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
        $this->ensureStatus(SaleStatus::Pending, 'confirmed');

        if ($this->lines === []) {
            throw new \DomainException('Cannot confirm a sale with no lines.');
        }

        $this->status = SaleStatus::Confirmed;
    }

    public function cancel(): void
    {
        $this->ensureStatus(SaleStatus::Pending, 'cancelled');

        $this->status = SaleStatus::Cancelled;
    }

    private function ensureEditable(): void
    {
        if ($this->status !== SaleStatus::Pending) {
            throw new \DomainException("Sale is not editable in status {$this->status->value}.");
        }
    }

    private function ensureStatus(SaleStatus $expected, string $action): void
    {
        if ($this->status !== $expected) {
            throw new \DomainException("Only {$expected->value} sales can be {$action}.");
        }
    }
}
