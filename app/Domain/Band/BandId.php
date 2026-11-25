<?php

declare(strict_types=1);

namespace App\Domain\Band;

final readonly class BandId
{
    public const int MINIMUM = 1;

    public function __construct(public int $value)
    {
        if ($this->value < self::MINIMUM) {
            throw InvalidBandData::invalidIdentifier($this->value);
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
