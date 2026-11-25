<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Band\CreateBand;
use App\Application\Band\CreateBandCommand;
use App\Domain\Band\Band;
use App\Domain\Band\BandId;
use App\Domain\Band\BandRepository;
use App\Domain\Band\Genre;
use App\Domain\Shared\DomainEvent;
use App\Domain\Shared\DomainEventDispatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateBand::class)]
#[CoversClass(CreateBandCommand::class)]
final class CreateBandTest extends TestCase
{
    public function test_command_creates_band_and_dispatches_one_event(): void
    {
        $repository = new class implements BandRepository
        {
            private int $nextId = 1;

            public function all(int $limit, int $offset): array
            {
                return [];
            }

            public function count(): int
            {
                return 0;
            }

            public function findById(BandId $id): ?Band
            {
                return null;
            }

            public function findByGenre(Genre $genre, int $limit, int $offset): array
            {
                return [];
            }

            public function countByGenre(Genre $genre): int
            {
                return 0;
            }

            public function save(Band $band): Band
            {
                return $band->withIdentity(new BandId($this->nextId++));
            }
        };

        $events = new class implements DomainEventDispatcher
        {
            /** @var list<DomainEvent> */
            public array $dispatched = [];

            public function dispatch(DomainEvent $event): void
            {
                $this->dispatched[] = $event;
            }
        };

        $band = (new CreateBand($repository, $events))(new CreateBandCommand(
            name: 'Daft Punk',
            genre: 'electronic',
            formedIn: 1993,
        ));

        self::assertSame(1, $band->id?->value);
        self::assertSame('Daft Punk', $band->name->value);
        self::assertCount(1, $events->dispatched);
        self::assertCount(1, $band->events);
    }
}
