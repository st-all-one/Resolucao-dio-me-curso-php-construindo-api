<?php

declare(strict_types=1);

namespace App\Domain\Band;

final class InvalidBandData extends \InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function emptyName(): self
    {
        return new self('Band name must not be empty.');
    }

    public static function nameTooLong(int $maximum): self
    {
        return new self(sprintf('Band name must not exceed %d characters.', $maximum));
    }

    public static function invalidGenre(string $value): self
    {
        return new self(sprintf('Unknown genre "%s".', $value));
    }

    public static function yearOutOfRange(int $minimum, int $maximum): self
    {
        return new self(sprintf('Formation year must be between %d and %d.', $minimum, $maximum));
    }

    public static function invalidIdentifier(int $identifier): self
    {
        return new self(sprintf('Band id must be a positive integer, got %d.', $identifier));
    }
}
