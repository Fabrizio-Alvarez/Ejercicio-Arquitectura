<?php

declare(strict_types=1);

namespace Supermarket\Domain\Shared;

/**
 * Money value object.
 *
 * The amount is stored as INTEGER CENTS to avoid floating-point drift
 * (the classic "0.1 + 0.2 != 0.3" bug). Money is immutable: every
 * arithmetic operation returns a new instance.
 */
final class Money
{
    public function __construct(
        private readonly int $amount,
        private readonly string $currency,
    ) {}

    /**
     * Build a Money from a decimal string (e.g. "10.99") without float drift.
     * Parsing uses string manipulation, never float arithmetic.
     */
    public static function fromDecimal(string $decimal, string $currency): self
    {
        if (!preg_match('/^-?\d+(\.\d{1,2})?$/', $decimal)) {
            throw new \InvalidArgumentException("Invalid decimal amount: {$decimal}");
        }

        $parts = explode('.', $decimal);
        $whole = (int) $parts[0];
        $cents = isset($parts[1]) ? str_pad($parts[1], 2, '0') : '00';

        return new self($whole * 100 + (int) $cents, $currency);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        $this->ensureSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->ensureSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(int $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    /**
     * Apply a percentage OFF (e.g. 25 = 25% discount) and return the
     * resulting Money, rounded half-up on the cent.
     */
    public function applyPercent(float $percent): self
    {
        $result = (int) round($this->amount * (100 - $percent) / 100, 0, PHP_ROUND_HALF_UP);

        return new self($result, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        $sign = $this->amount < 0 ? '-' : '';

        return $sign . number_format(abs($this->amount) / 100, 2, '.', '') . ' ' . $this->currency;
    }

    private function ensureSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException($this->currency, $other->currency);
        }
    }
}
