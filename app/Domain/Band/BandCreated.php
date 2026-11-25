<?php

declare(strict_types=1);

namespace App\Domain\Band;

use App\Domain\Shared\DomainEvent;

final readonly class BandCreated implements DomainEvent
{
    public function __construct(
        public BandId $id,
        public BandName $name,
        public Genre $genre,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable,
    ) {}

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
