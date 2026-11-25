<?php

declare(strict_types=1);

namespace App\Domain\Band;

final class BandNotFound extends \RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withId(BandId $id): self
    {
        return new self(sprintf('Band %s was not found.', $id));
    }

    public static function withGenre(Genre $genre): self
    {
        return new self(sprintf('No bands found for genre "%s".', $genre->value));
    }
}
