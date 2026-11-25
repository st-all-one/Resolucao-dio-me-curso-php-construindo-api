<?php

declare(strict_types=1);

namespace App\Domain\Band;

final readonly class BandName
{
    public const int MAXIMUM_LENGTH = 120;

    public function __construct(public string $value)
    {
        $trimmed = trim($this->value);

        if ($trimmed === '') {
            throw InvalidBandData::emptyName();
        }

        if (mb_strlen($trimmed) > self::MAXIMUM_LENGTH) {
            throw InvalidBandData::nameTooLong(self::MAXIMUM_LENGTH);
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
