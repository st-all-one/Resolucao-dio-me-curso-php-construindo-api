<?php

declare(strict_types=1);

namespace App\Domain\Band;

final readonly class FormedIn
{
    public const int MINIMUM_YEAR = 1900;

    public function __construct(public int $value)
    {
        if ($this->value < self::MINIMUM_YEAR || $this->value > self::maximumYear()) {
            throw InvalidBandData::yearOutOfRange(self::MINIMUM_YEAR, self::maximumYear());
        }
    }

    public static function maximumYear(): int
    {
        return (int) date('Y');
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
